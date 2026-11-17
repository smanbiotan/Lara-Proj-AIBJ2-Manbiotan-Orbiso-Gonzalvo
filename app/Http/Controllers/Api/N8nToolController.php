<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Models\Appointment;
use App\Models\AutomationLog;
use App\Models\ChatbotConversation;
use App\Models\Inquiry;
use App\Models\Service;
use App\Models\Setting;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Authorised tools for n8n (all routes use the n8n.token middleware).
 * The AI never touches the database directly: it can only call these narrow endpoints,
 * and every booking goes through BookingService validation.
 */
class N8nToolController extends Controller
{
    public function services()
    {
        return response()->json(['services' => Service::active()->ordered()->get()->map->toPublicArray()]);
    }

    public function businessInfo(AvailabilityService $availability)
    {
        $s = Setting::allValues();
        $hours = $availability->businessHours();
        $names = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        return response()->json([
            'business_name' => $s['business_name'],
            'email' => $s['business_email'] ?: null,
            'phone' => $s['business_phone'] ?: null,
            'address' => $s['business_address'] ?: null,
            'contact_details_note' => ($s['business_email'] || $s['business_phone'] || $s['business_address']) ? null : 'Contact details have not been published yet. Direct customers to the website Contact page.',
            'opening_time' => date('g:i A', strtotime($hours['opening_time'])),
            'closing_time' => date('g:i A', strtotime($hours['closing_time'])),
            'open_days' => array_map(fn ($d) => $names[$d], $hours['open_days']),
            'hours_are_sample' => $hours['is_sample'],
            'booking_policy' => 'Bookings are created as Pending and are only Confirmed after a salon administrator approves them.',
            'today' => now()->toDateString(),
            'timezone' => config('app.timezone'),
        ]);
    }

    public function availability(Request $request, AvailabilityService $availability)
    {
        $data = $request->validate([
            'service' => ['required', 'string'],
            'date' => ['required', 'date_format:Y-m-d'],
        ]);
        $service = $this->findService($data['service']);
        if (!$service) return response()->json(['error' => 'Unknown service. Call get_services for valid slugs.'], 422);
        if (Carbon::parse($data['date'])->lt(today())) return response()->json(['error' => 'That date is in the past.'], 422);

        return response()->json([
            'service' => $service->name, 'service_slug' => $service->slug, 'date' => $data['date'],
            'open' => $availability->isOpenOn(Carbon::parse($data['date'])),
            'available_times' => array_map(fn ($t) => ['time' => $t, 'label' => date('g:i A', strtotime($t))], $availability->availableTimes($service, $data['date'])),
        ]);
    }

    public function createAppointment(Request $request, BookingService $bookings)
    {
        $input = $request->all();
        if (isset($input['service']) && ($s = $this->findService((string) $input['service']))) $input['service'] = $s->slug;
        if (isset($input['appointment_time']) && ($t = strtotime((string) $input['appointment_time'])) !== false) $input['appointment_time'] = date('H:i', $t);
        if (isset($input['email'])) $input['email'] = strtolower(trim((string) $input['email']));

        $validator = Validator::make($input, BookingRequest::baseRules(), BookingRequest::messagesList());
        if ($validator->fails()) {
            return response()->json(['created' => false, 'errors' => $validator->errors()->all()], 422);
        }

        try {
            $appointment = $bookings->create(BookingRequest::toBookingData($validator->validated()), 'chatbot');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['created' => false, 'errors' => collect($e->errors())->flatten()->all()], 422);
        }

        return response()->json([
            'created' => true,
            'reference' => $appointment->reference,
            'status' => $appointment->status,
            'service' => $appointment->service_name,
            'date' => $appointment->appointment_date->format('l, F j, Y'),
            'time' => $appointment->time_label,
            'note' => 'The booking is Pending. The salon will confirm it by email.',
        ], 201);
    }

    /** Requires BOTH reference and the email used for booking (customer verification). */
    public function appointmentStatus(Request $request)
    {
        $data = $request->validate(['reference' => ['required', 'string', 'max:20'], 'email' => ['required', 'email']]);
        $appointment = Appointment::with('customer', 'serviceRecord')->where('reference', strtoupper(trim($data['reference'])))->first();

        if (!$appointment || strtolower($appointment->customer?->email ?? '') !== strtolower(trim($data['email']))) {
            return response()->json(['found' => false, 'message' => 'No appointment matches that reference and email.'], 404);
        }

        return response()->json([
            'found' => true, 'reference' => $appointment->reference, 'status' => $appointment->status,
            'service' => $appointment->service_name, 'date' => $appointment->appointment_date->format('l, F j, Y'),
            'time' => $appointment->time_label,
        ]);
    }

    public function escalate(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:3000'],
            'session_id' => ['nullable', 'string', 'max:64'],
        ]);

        $inquiry = Inquiry::create([
            'name' => $data['name'], 'email' => strtolower($data['email']), 'subject' => 'Chat assistant escalation',
            'message' => $data['message'], 'status' => 'New', 'source' => 'chatbot',
        ]);
        if (!empty($data['session_id'])) {
            ChatbotConversation::where('session_token', $data['session_id'])->update(['status' => 'escalated', 'escalated_at' => now()]);
        }

        return response()->json(['escalated' => true, 'inquiry_id' => $inquiry->id, 'message' => 'A salon team member will follow up by email.'], 201);
    }

    /**
     * Workflow 3: returns confirmed appointments that are due for a reminder and atomically
     * marks them "processing" so overlapping runs cannot send duplicates.
     */
    public function claimReminders(Request $request)
    {
        $hours = (int) $request->input('hours_before', Setting::get('reminder_hours_before', 24));
        $hours = max(1, min(168, $hours));
        $windowEnd = now()->addHours($hours);

        $claimed = DB::transaction(function () use ($windowEnd) {
            $candidates = Appointment::with('customer', 'serviceRecord')
                ->where('status', 'Confirmed')
                ->whereNull('reminder_sent_at')
                ->whereDate('appointment_date', '>=', today())
                ->whereDate('appointment_date', '<=', $windowEnd->toDateString())
                ->where(fn ($q) => $q->whereNull('reminder_claimed_at')->orWhere('reminder_claimed_at', '<', now()->subMinutes(30)))
                ->lockForUpdate()->get()
                ->filter(fn (Appointment $a) => $a->starts_at->isFuture() && $a->starts_at->lte($windowEnd));

            foreach ($claimed = $candidates->values() as $appointment) {
                $appointment->update(['reminder_status' => 'processing', 'reminder_claimed_at' => now()]);
            }
            return $claimed;
        });

        $items = $claimed->map(function (Appointment $a) {
            $log = AutomationLog::create([
                'event_id' => (string) Str::uuid(), 'workflow' => 'reminder', 'event' => 'appointment.reminder',
                'appointment_id' => $a->id, 'appointment_reference' => $a->reference, 'status' => 'pending',
                'message' => 'Claimed by n8n reminder workflow.',
            ]);
            return ['event_id' => $log->event_id] + $a->toAutomationPayload();
        })->values();

        return response()->json(['count' => $items->count(), 'hours_before' => $hours, 'appointments' => $items]);
    }

    /** n8n reports the real outcome of an email (appointment email or reminder). */
    public function automationResult(Request $request)
    {
        $data = $request->validate([
            'event_id' => ['required', 'uuid'],
            'success' => ['required', 'boolean'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $log = AutomationLog::where('event_id', $data['event_id'])->first();
        if (!$log) return response()->json(['message' => 'Unknown event_id.'], 404);

        $log->update([
            'status' => $data['success'] ? 'email_sent' : 'email_failed',
            'message' => $data['message'] ?? ($data['success'] ? 'n8n reported the email as sent.' : 'n8n reported an email failure.'),
            'completed_at' => now(),
        ]);

        if ($log->workflow === 'reminder' && $log->appointment) {
            $log->appointment->update($data['success']
                ? ['reminder_status' => 'sent', 'reminder_sent_at' => now()]
                : ['reminder_status' => 'failed', 'reminder_claimed_at' => null]);
        }

        return response()->json(['recorded' => true, 'status' => $log->status]);
    }

    private function findService(string $value): ?Service
    {
        $value = trim($value);
        return Service::active()->where('slug', Str::slug($value))->first()
            ?? Service::active()->whereRaw('LOWER(name) = ?', [mb_strtolower($value)])->first();
    }
}
