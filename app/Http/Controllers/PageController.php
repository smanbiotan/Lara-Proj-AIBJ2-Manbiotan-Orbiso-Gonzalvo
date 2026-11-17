<?php

namespace App\Http\Controllers;

use App\Models\Inquiry;
use App\Models\Service;
use App\Models\Setting;
use App\Services\AvailabilityService;
use Illuminate\Http\Request;

/**
 * Home  – Member 1 (Sherwin Alvin Manbiotan)
 * About – Member 2 (Gerald EJ Gonzalvo)
 * Contact – Member 3 (Anthony Josh Orbiso)
 */
class PageController extends Controller
{
    public function home()
    {
        return view('home', [
            'featuredServices' => Service::active()->ordered()->take(6)->get(),
            'settings' => Setting::allValues(),
        ]);
    }

    public function about()
    {
        return view('about', [
            'serviceCount' => Service::active()->count(),
        ]);
    }

    public function contact(AvailabilityService $availability)
    {
        return view('contact', [
            'settings' => Setting::allValues(),
            'hours' => $availability->businessHours(),
        ]);
    }

    /** Saves a contact inquiry. Responds with JSON for the JavaScript form, or redirects without JS. */
    public function contactStore(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'min:3', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'website' => ['nullable', 'max:0'], // honeypot field: must stay empty
        ], ['website.max' => 'Your message could not be sent.']);

        unset($data['website']);
        Inquiry::create($data + ['status' => 'New', 'source' => 'contact_form']);

        $message = 'Thank you, '.$data['name'].'! Your message has been sent. Our team will get back to you soon.';
        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }
        return redirect('/contact')->with('success', $message);
    }
}
