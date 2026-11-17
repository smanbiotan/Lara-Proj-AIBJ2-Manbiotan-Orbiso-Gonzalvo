<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Requests\BookingRequest;
use App\Models\Appointment;
use App\Models\Service;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Book Now flow
 *  1. Customer picks a service, a date on the calendar, a time, and fills in their details.
 *  2. Guests: the details are saved in the session and a "Sign in to continue" dialog appears
 *     (Continue with Google, email sign-in, or create an account).
 *  3. After signing in they land on the Review page and confirm the appointment.
 *  Signed-in customers confirm straight from the form.
 */
class BookingController extends Controller
{
    private const DRAFT_KEY = 'booking_draft';

    public function create(Request $request, AvailabilityService $availability)
    {
        $services = Service::active()->ordered()->get();
        $customer = Auth::guard('customer')->user();
        $draft = $request->session()->get(self::DRAFT_KEY, []);
        $selected = old('service', $draft['service'] ?? $request->query('service'));

        return view('booking.create', [
            'services' => $services,
            'selectedSlug' => $services->contains('slug', $selected) ? $selected : null,
            'customer' => $customer,
            'draft' => $draft,
            'hours' => $availability->businessHours(),
            'today' => now()->toDateString(),
            'maxDate' => now()->addMonths(6)->toDateString(),
            'submissionToken' => old('submission_token', $draft['submission_token'] ?? (string) Str::uuid()),
            'showAuthGate' => !$customer && (session('auth_gate') || $request->query('step') === 'sign-in') && $draft,
            'googleEnabled' => GoogleAuthController::enabled(),
        ]);
    }

    public function store(BookingRequest $request, BookingService $bookings)
    {
        $customer = Auth::guard('customer')->user();
        $validated = $request->validated();

        if (!$customer) {
            // Check the time now, so nobody signs in only to learn the slot is not available.
            $service = Service::active()->where('slug', $validated['service'])->firstOrFail();
            if (!app(AvailabilityService::class)->isAvailable($service, $validated['appointment_date'], $validated['appointment_time'])) {
                throw ValidationException::withMessages(['appointment_time' => 'That time is not available. Please choose another slot.']);
            }

            // Not signed in yet: keep the details, then ask the customer to sign in or sign up.
            $validated['submission_token'] = ($validated['submission_token'] ?? null) ?: (string) Str::uuid();
            $request->session()->put(self::DRAFT_KEY, $validated);
            $request->session()->put('url.intended', route('booking.review'));

            return redirect()->route('booking', ['step' => 'sign-in'])->with('auth_gate', true);
        }

        $validated['email'] = $customer->email;
        $appointment = $bookings->create(BookingRequest::toBookingData($validated), 'website', $customer);

        return $this->finish($request, $appointment);
    }

    /** Signed-in customers review the saved details before confirming. */
    public function review(Request $request, AvailabilityService $availability)
    {
        $draft = $request->session()->get(self::DRAFT_KEY);
        if (!$draft) return redirect()->route('booking');

        $service = Service::active()->where('slug', $draft['service'])->first();
        if (!$service) {
            return redirect()->route('booking')->withErrors(['service' => 'That service is no longer available. Please choose another.']);
        }

        $customer = Auth::guard('customer')->user();
        $stillAvailable = $availability->isAvailable($service, $draft['appointment_date'], $draft['appointment_time']);

        return view('booking.review', [
            'draft' => $draft,
            'service' => $service,
            'customer' => $customer,
            'dateLabel' => Carbon::parse($draft['appointment_date'])->format('l, F j, Y'),
            'timeLabel' => Carbon::parse($draft['appointment_time'])->format('g:i A'),
            'stillAvailable' => $stillAvailable,
        ]);
    }

    public function confirm(Request $request, BookingService $bookings)
    {
        $draft = $request->session()->get(self::DRAFT_KEY);
        if (!$draft) return redirect()->route('booking');

        $customer = Auth::guard('customer')->user();
        $data = $draft;
        $data['email'] = $customer->email;
        if (!$customer->phone && !empty($draft['phone'])) $customer->update(['phone' => $draft['phone']]);

        try {
            $appointment = $bookings->create(BookingRequest::toBookingData($data), 'website', $customer);
        } catch (ValidationException $e) {
            // Slot taken in the meantime: send them back to the calendar with everything else kept.
            $draft['appointment_time'] = null;
            $request->session()->put(self::DRAFT_KEY, $draft);
            return redirect()->route('booking')->withErrors($e->errors());
        }

        return $this->finish($request, $appointment);
    }

    public function confirmed(Request $request, string $reference)
    {
        $customer = Auth::guard('customer')->user();
        $appointment = Appointment::with('serviceRecord', 'customer')->where('reference', $reference)->firstOrFail();

        $allowed = $request->session()->get('last_booking_reference') === $reference
            || ($customer && $appointment->customer_id === $customer->id);
        abort_unless($allowed, 404);

        return view('booking.confirmed', compact('appointment'));
    }

    private function finish(Request $request, Appointment $appointment)
    {
        $request->session()->forget([self::DRAFT_KEY, 'url.intended']);
        // Only the browser that made the booking may view its confirmation page.
        $request->session()->put('last_booking_reference', $appointment->reference);

        return redirect()->route('booking.confirmed', $appointment->reference);
    }
}
