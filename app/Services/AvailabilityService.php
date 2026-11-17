<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\Setting;
use Carbon\Carbon;

/**
 * Calculates real appointment availability from business hours and existing bookings.
 *
 * Resource rule: each service category is one bookable station (hair chair, nail station,
 * facial room, massage room, makeup station). Two Pending/Confirmed appointments in the same
 * category may not overlap in time. Different categories can run at the same time.
 */
class AvailabilityService
{
    public function businessHours(): array
    {
        $s = Setting::allValues();
        return [
            'opening_time' => $s['opening_time'] ?? '09:00',
            'closing_time' => $s['closing_time'] ?? '18:00',
            'open_days' => array_map('intval', array_filter(explode(',', (string) ($s['open_days'] ?? '')), 'strlen')),
            'slot_interval' => max(15, (int) ($s['slot_interval'] ?? 30)),
            'is_sample' => ($s['hours_are_sample'] ?? '1') === '1',
        ];
    }

    public function isOpenOn(Carbon $date): bool
    {
        return in_array($date->dayOfWeek, $this->businessHours()['open_days'], true);
    }

    /**
     * @return array<int, array{time:string,label:string,available:bool,reason:?string}>
     */
    public function slots(Service $service, string $date, ?int $exceptAppointmentId = null): array
    {
        $hours = $this->businessHours();
        $day = Carbon::parse($date)->startOfDay();
        if (!$this->isOpenOn($day) || $day->lt(today())) return [];

        $open = $day->copy()->setTimeFromTimeString($hours['opening_time']);
        $close = $day->copy()->setTimeFromTimeString($hours['closing_time']);
        $busy = $this->busyIntervals($service, $day->toDateString(), $exceptAppointmentId);

        $slots = [];
        for ($start = $open->copy(); $start->copy()->addMinutes($service->duration_minutes)->lte($close); $start->addMinutes($hours['slot_interval'])) {
            $end = $start->copy()->addMinutes($service->duration_minutes);
            $reason = null;
            if ($start->lte(now())) {
                $reason = 'past';
            } else {
                foreach ($busy as [$bStart, $bEnd]) {
                    if ($start->lt($bEnd) && $end->gt($bStart)) { $reason = 'booked'; break; }
                }
            }
            $slots[] = ['time' => $start->format('H:i'), 'label' => $start->format('g:i A'), 'available' => $reason === null, 'reason' => $reason];
        }
        return $slots;
    }

    public function availableTimes(Service $service, string $date, ?int $exceptAppointmentId = null): array
    {
        return array_values(array_map(fn ($s) => $s['time'], array_filter($this->slots($service, $date, $exceptAppointmentId), fn ($s) => $s['available'])));
    }

    public function isAvailable(Service $service, string $date, string $time, ?int $exceptAppointmentId = null): bool
    {
        return in_array(Carbon::parse($time)->format('H:i'), $this->availableTimes($service, $date, $exceptAppointmentId), true);
    }

    /** Busy [start, end] pairs for the service's station (category) on a date. */
    private function busyIntervals(Service $service, string $date, ?int $exceptAppointmentId): array
    {
        $categoryServiceIds = Service::where('category', $service->category)->pluck('id');
        $categoryNames = Service::where('category', $service->category)->pluck('name');

        return Appointment::active()
            ->whereDate('appointment_date', $date)
            ->when($exceptAppointmentId, fn ($q) => $q->where('id', '!=', $exceptAppointmentId))
            ->where(fn ($q) => $q->whereIn('service_id', $categoryServiceIds)
                ->orWhere(fn ($q) => $q->whereNull('service_id')->whereIn('service', $categoryNames)))
            ->get()
            ->map(fn (Appointment $a) => [$a->starts_at, $a->ends_at])
            ->all();
    }
}
