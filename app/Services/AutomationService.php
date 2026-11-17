<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AutomationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

/**
 * Laravel → n8n bridge. Every call is logged in automation_logs.
 *
 * Status meanings (important for honest reporting):
 *  - sent_to_n8n: n8n accepted the webhook. This is NOT proof an email was delivered.
 *  - email_sent / email_failed: reported back by n8n through /api/n8n/automation-results.
 *  - skipped: the n8n URL is not configured, so nothing was sent.
 */
class AutomationService
{
    public function appointmentEvent(string $event, Appointment $appointment, array $extra = []): AutomationLog
    {
        // One email per appointment per status (or per creation). Prevents duplicate notifications.
        $key = match ($event) {
            'appointment.created' => "appointment.created:{$appointment->id}",
            'appointment.status_changed' => "appointment.status_changed:{$appointment->id}:{$appointment->status}",
            default => "{$event}:{$appointment->id}:".now()->format('YmdHis'),
        };

        $existing = AutomationLog::where('idempotency_key', $key)->first();
        if ($existing && $existing->status !== 'failed' && $existing->status !== 'skipped') {
            return $existing;
        }

        $log = $existing ?? new AutomationLog(['event_id' => (string) Str::uuid(), 'idempotency_key' => $key]);
        $payload = [
            'event_id' => $log->event_id,
            'event' => $event,
            'occurred_at' => now()->toIso8601String(),
            'business_name' => config('app.name'),
            'appointment' => $appointment->toAutomationPayload(),
        ] + $extra;

        $log->fill([
            'workflow' => 'appointment_email', 'event' => $event, 'appointment_id' => $appointment->id,
            'appointment_reference' => $appointment->reference, 'payload' => $payload, 'status' => 'pending',
        ])->save();

        $url = config('salon.n8n.appointment_webhook_url');
        if (!$url) {
            $log->update(['status' => 'skipped', 'message' => 'N8N_APPOINTMENT_WEBHOOK_URL is not configured.']);
            return $log;
        }

        try {
            $response = Http::timeout(config('salon.n8n.timeout'))
                ->withHeaders(['X-Webhook-Secret' => (string) config('salon.n8n.webhook_secret')])
                ->acceptJson()->post($url, $payload);
            $log->update([
                'status' => $response->successful() ? 'sent_to_n8n' : 'failed',
                'http_status' => $response->status(),
                'message' => $response->successful() ? 'Webhook accepted by n8n. Waiting for email result.' : Str::limit($response->body(), 500),
            ]);
        } catch (Throwable $e) {
            report($e);
            $log->update(['status' => 'failed', 'message' => 'n8n unreachable: '.Str::limit($e->getMessage(), 300)]);
        }

        return $log;
    }

    /**
     * Sends a chat message to n8n Workflow 1. Returns the reply text, or null if n8n is
     * unavailable (the caller then uses the database-backed fallback).
     */
    public function chat(string $message, string $sessionToken, array $history, ?array $customer = null): ?string
    {
        $url = config('salon.n8n.chat_webhook_url');
        $log = AutomationLog::create([
            'event_id' => (string) Str::uuid(), 'workflow' => 'assistant', 'event' => 'chat.request', 'status' => 'pending',
            'payload' => ['session' => substr($sessionToken, 0, 8).'…', 'chars' => mb_strlen($message)],
        ]);

        if (!$url) {
            $log->update(['status' => 'skipped', 'message' => 'N8N_CHAT_WEBHOOK_URL is not configured. Fallback answer used.']);
            return null;
        }

        try {
            $response = Http::timeout(config('salon.n8n.chat_timeout'))
                ->withHeaders(['X-Webhook-Secret' => (string) config('salon.n8n.webhook_secret')])
                ->acceptJson()->post($url, [
                    'message' => $message,
                    'session_id' => $sessionToken,
                    'history' => $history,
                    'customer' => $customer,
                    'today' => now()->toDateString(),
                    'timezone' => config('app.timezone'),
                    // Salon facts sent with every message, so the assistant can answer without calling back into Laravel.
                    'salon' => $this->salonFacts(),
                ]);

            $reply = $response->successful() ? trim((string) ($response->json('reply') ?? $response->json('output') ?? '')) : '';
            if ($reply === '') {
                $log->update(['status' => 'failed', 'http_status' => $response->status(), 'message' => 'Empty or error response from n8n: '.Str::limit($response->body(), 300)]);
                return null;
            }
            $log->update(['status' => 'success', 'http_status' => $response->status(), 'message' => 'Reply generated by n8n + Gemini.', 'completed_at' => now()]);
            return Str::limit($reply, 4000);
        } catch (Throwable $e) {
            report($e);
            $log->update(['status' => 'failed', 'message' => 'n8n unreachable: '.Str::limit($e->getMessage(), 300)]);
            return null;
        }
    }

    /** Services, prices, hours, contact details and booking steps from the database (same data as the /api/n8n tools). */
    private function salonFacts(): array
    {
        try {
            $tools = app(\App\Http\Controllers\Api\N8nToolController::class);
            return [
                'services' => $tools->services()->getData(true)['services'] ?? [],
                'business' => $tools->businessInfo(app(\App\Services\AvailabilityService::class))->getData(true),
                'website' => rtrim((string) config('app.url'), '/'),
                'how_to_book' => [
                    'Click "Book Now" on the website.',
                    'Choose a service.',
                    'Pick a date on the calendar and an available time.',
                    'Enter your name, email and contact number.',
                    'Sign in with Google or email, or create an account, then confirm.',
                    'You get a booking reference. The booking is Pending until the salon confirms it by email.',
                ],
                'check_status' => 'Signed-in customers see their bookings under "My Account". Others can use the Contact page.',
            ];
        } catch (Throwable $e) {
            report($e);
            return [];
        }
    }
}
