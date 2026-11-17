<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AutomationLog;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q'));
        $appointments = Appointment::with('customer', 'serviceRecord')
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w->where('reference', 'like', "%{$q}%")
                ->orWhere('service', 'like', "%{$q}%")
                ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"))))
            ->when(in_array($request->query('status'), Appointment::STATUSES, true), fn ($query) => $query->where('status', $request->query('status')))
            ->when($request->query('date'), fn ($query, $date) => $query->whereDate('appointment_date', $date))
            ->orderByDesc('appointment_date')->orderByDesc('appointment_time')
            ->paginate(15)->withQueryString();

        $counts = Appointment::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.appointments.index', compact('appointments', 'counts'));
    }

    public function show(Appointment $appointment, AvailabilityService $availability)
    {
        $appointment->load('customer', 'serviceRecord');
        $logs = AutomationLog::where('appointment_id', $appointment->id)->latest()->get();
        return view('admin.appointments.show', compact('appointment', 'logs'));
    }

    public function updateStatus(Request $request, Appointment $appointment, BookingService $bookings)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Appointment::STATUSES)],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $bookings->changeStatus($appointment, $data['status'], $data['admin_notes'] ?? null);

        return back()->with('success', "Appointment {$appointment->reference} is now {$appointment->status}.");
    }

    public function reschedule(Request $request, Appointment $appointment, BookingService $bookings)
    {
        $data = $request->validate([
            'appointment_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i'],
        ]);
        abort_unless(in_array($appointment->status, Appointment::ACTIVE_STATUSES, true), 422, 'Only pending or confirmed appointments can be rescheduled.');
        $bookings->reschedule($appointment, $data['appointment_date'], $data['appointment_time']);

        return back()->with('success', "Appointment {$appointment->reference} was rescheduled.");
    }
}
