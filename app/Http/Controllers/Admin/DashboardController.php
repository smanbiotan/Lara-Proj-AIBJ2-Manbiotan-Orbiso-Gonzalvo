<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Inquiry;
use App\Models\Service;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function __invoke()
    {
        // Bookings per week for the last 8 weeks (by appointment date), computed in PHP so it works on MySQL and SQLite.
        $start = now()->startOfWeek()->subWeeks(7);
        $byWeek = Appointment::whereDate('appointment_date', '>=', $start)->whereDate('appointment_date', '<', now()->startOfWeek()->addWeek())
            ->get(['appointment_date'])->groupBy(fn ($a) => $a->appointment_date->copy()->startOfWeek()->format('Y-m-d'))->map->count();
        $weekly = collect(range(0, 7))->map(function ($i) use ($start, $byWeek) {
            $week = $start->copy()->addWeeks($i);
            return ['label' => $week->format('M j'), 'count' => $byWeek[$week->format('Y-m-d')] ?? 0];
        });

        $popular = Service::withCount('appointments')->orderByDesc('appointments_count')->take(6)->get(['id', 'name']);

        return view('admin.dashboard', [
            'stats' => [
                'customers' => Customer::count(),
                'appointments' => Appointment::count(),
                'pending' => Appointment::where('status', 'Pending')->count(),
                'confirmed' => Appointment::where('status', 'Confirmed')->count(),
                'openInquiries' => Inquiry::where('status', '!=', 'Resolved')->count(),
                'today' => Appointment::whereDate('appointment_date', today())->active()->count(),
            ],
            'recent' => Appointment::with('customer', 'serviceRecord')->latest()->take(8)->get(),
            'upcoming' => Appointment::with('customer', 'serviceRecord')->active()->whereDate('appointment_date', '>=', today())
                ->orderBy('appointment_date')->orderBy('appointment_time')->take(20)->get()
                ->filter(fn ($a) => $a->starts_at->isFuture())->take(5),
            'weekly' => $weekly,
            'popular' => $popular,
        ]);
    }
}
