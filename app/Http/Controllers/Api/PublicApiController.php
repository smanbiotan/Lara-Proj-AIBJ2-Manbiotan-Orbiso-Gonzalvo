<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Models\Service;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use Illuminate\Http\Request;

/** Public JSON endpoints used by the website's JavaScript (no private data). */
class PublicApiController extends Controller
{
    public function services()
    {
        return response()->json(['data' => Service::active()->ordered()->get()->map->toPublicArray()]);
    }

    public function availability(Request $request, AvailabilityService $availability)
    {
        $data = $request->validate([
            'service' => ['required', 'string', 'exists:services,slug'],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', 'before_or_equal:'.now()->addMonths(6)->toDateString()],
        ]);
        $service = Service::active()->where('slug', $data['service'])->firstOrFail();
        $slots = $availability->slots($service, $data['date']);

        return response()->json([
            'service' => $service->name,
            'date' => $data['date'],
            'duration_minutes' => $service->duration_minutes,
            'open' => $availability->isOpenOn(\Carbon\Carbon::parse($data['date'])),
            'slots' => $slots,
            'available_count' => count(array_filter($slots, fn ($s) => $s['available'])),
        ]);
    }

    public function storeAppointment(BookingRequest $request, BookingService $bookings)
    {
        $appointment = $bookings->create(BookingRequest::toBookingData($request->validated()), 'api');

        return response()->json([
            'message' => 'Your booking request has been received. It is Pending until the salon confirms it.',
            'reference' => $appointment->reference,
            'status' => $appointment->status,
        ], 201);
    }
}
