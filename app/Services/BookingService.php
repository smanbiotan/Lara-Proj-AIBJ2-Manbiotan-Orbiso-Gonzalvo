<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * The ONE place where appointments are created or rescheduled.
 * Used by the booking form, the public JSON API, the admin portal, and the n8n chatbot tool,
 * so the AI assistant can never bypass Laravel's validation.
 */
class BookingService
{
    public function __construct(private AvailabilityService $availability, private AutomationService $automation)
    {
    }

    /**
     * @param array{name:string,email:string,phone:?string,service:Service,appointment_date:string,appointment_time:string,notes:?string,submission_token:?string} $data
     */
    public function create(array $data, string $source = 'website', ?Customer $signedIn = null): Appointment
    {
        // Duplicate submission (double click / refresh): return the original booking.
        if (!empty($data['submission_token'])) {
            $existing = Appointment::where('submission_token', $data['submission_token'])->first();
            if ($existing) return $existing;
        }

        /** @var Service $service */
        $service = $data['service'];

        $appointment = DB::transaction(function () use ($data, $service, $source, $signedIn) {
            // Lock the station's service rows so two simultaneous requests cannot take the same slot.
            Service::where('category', $service->category)->lockForUpdate()->get();

            if (!$service->is_active) {
                throw ValidationException::withMessages(['service' => 'This service is not currently available for booking.']);
            }
            if (!$this->availability->isAvailable($service, $data['appointment_date'], $data['appointment_time'])) {
                throw ValidationException::withMessages(['appointment_time' => 'That time is no longer available. Please choose another slot.']);
            }

            $customer = $signedIn ?? Customer::firstOrCreate(
                ['email' => strtolower(trim($data['email']))],
                ['name' => $data['name'], 'phone' => $data['phone'] ?? null, 'status' => 'New']
            );
            // Fill a missing phone number, but never overwrite existing customer details from a public form.
            if (!$customer->phone && !empty($data['phone'])) $customer->update(['phone' => $data['phone']]);

            return $customer->appointments()->create([
                'service_id' => $service->id,
                'service' => $service->name,
                'appointment_date' => $data['appointment_date'],
                'appointment_time' => date('H:i', strtotime($data['appointment_time'])),
                'duration_minutes' => $service->duration_minutes,
                'status' => 'Pending',
                'notes' => $data['notes'] ?? null,
                'source' => $source,
                'submission_token' => $data['submission_token'] ?? null,
                'status_changed_at' => now(),
            ]);
        });

        // Outside the transaction: n8n being offline must never block a booking.
        $this->automation->appointmentEvent('appointment.created', $appointment);

        return $appointment;
    }

    public function changeStatus(Appointment $appointment, string $status, ?string $adminNotes = null): Appointment
    {
        if (!in_array($status, Appointment::STATUSES, true)) {
            throw ValidationException::withMessages(['status' => 'Invalid appointment status.']);
        }
        $previous = $appointment->status;

        DB::transaction(function () use ($appointment, $status, $previous, $adminNotes) {
            $service = $appointment->serviceRecord;
            if ($service) Service::where('category', $service->category)->lockForUpdate()->get();

            // Re-activating a cancelled/rejected booking must not double-book its slot.
            if ($service && in_array($status, Appointment::ACTIVE_STATUSES, true) && !in_array($previous, Appointment::ACTIVE_STATUSES, true)
                && !$this->availability->isAvailable($service, $appointment->appointment_date->format('Y-m-d'), $appointment->appointment_time, $appointment->id)) {
                throw ValidationException::withMessages(['status' => 'Another booking already uses this time slot.']);
            }

            $appointment->fill(['status' => $status]);
            if ($adminNotes !== null) $appointment->admin_notes = $adminNotes;
            if ($previous !== $status) $appointment->status_changed_at = now();
            $appointment->save();
        });

        if ($previous !== $status) {
            $this->automation->appointmentEvent('appointment.status_changed', $appointment, ['previous_status' => $previous]);
        }
        return $appointment;
    }

    public function reschedule(Appointment $appointment, string $date, string $time): Appointment
    {
        $service = $appointment->serviceRecord;
        if (!$service) throw ValidationException::withMessages(['appointment_date' => 'This appointment is not linked to a service.']);

        DB::transaction(function () use ($appointment, $service, $date, $time) {
            Service::where('category', $service->category)->lockForUpdate()->get();
            if (!$this->availability->isAvailable($service, $date, $time, $appointment->id)) {
                throw ValidationException::withMessages(['appointment_time' => 'The selected new time is not available.']);
            }
            $appointment->update([
                'appointment_date' => $date, 'appointment_time' => date('H:i', strtotime($time)),
                // A new time needs a new reminder.
                'reminder_status' => null, 'reminder_claimed_at' => null, 'reminder_sent_at' => null,
            ]);
        });

        $this->automation->appointmentEvent('appointment.rescheduled', $appointment);
        return $appointment;
    }
}
