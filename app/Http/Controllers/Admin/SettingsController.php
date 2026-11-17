<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function edit()
    {
        return view('admin.settings', ['settings' => Setting::allValues(), 'admin' => Auth::guard('admin')->user()]);
    }

    /** Administrator profile page (opened from the account menu). */
    public function profile()
    {
        $upcoming = \App\Models\Appointment::with('customer', 'serviceRecord')->active()
            ->whereDate('appointment_date', '>=', today())
            ->orderBy('appointment_date')->orderBy('appointment_time')->limit(5)->get();

        return view('admin.profile', ['admin' => Auth::guard('admin')->user(), 'upcoming' => $upcoming]);
    }

    /** Light / Dark switch in the account menu and Settings (saved per administrator). */
    public function updateTheme(Request $request)
    {
        $data = $request->validate(['theme' => ['required', Rule::in(['light', 'dark'])]]);
        Auth::guard('admin')->user()->update(['theme' => $data['theme']]);

        return $request->expectsJson()
            ? response()->json(['ok' => true, 'theme' => $data['theme']])
            : back()->with('success', 'Theme updated.');
    }

    public function updateBusiness(Request $request)
    {
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:120'],
            'business_tagline' => ['nullable', 'string', 'max:160'],
            'business_email' => ['nullable', 'email', 'max:255'],
            'business_phone' => ['nullable', 'string', 'max:40'],
            'business_address' => ['nullable', 'string', 'max:500'],
            'opening_time' => ['required', 'date_format:H:i'],
            'closing_time' => ['required', 'date_format:H:i', 'after:opening_time'],
            'open_days' => ['required', 'array', 'min:1'],
            'open_days.*' => ['integer', 'between:0,6'],
            'slot_interval' => ['required', Rule::in(['15', '30', '60'])],
            'reminder_hours_before' => ['required', 'integer', 'between:1,168'],
            'hours_are_sample' => ['nullable', 'boolean'],
        ]);
        $data['open_days'] = implode(',', array_unique(array_map('intval', $data['open_days'])));
        $data['hours_are_sample'] = $request->boolean('hours_are_sample') ? '1' : '0';
        foreach (['business_tagline', 'business_email', 'business_phone', 'business_address'] as $k) $data[$k] = $data[$k] ?? '';
        Setting::put($data);

        return back()->with('success', 'Business settings saved.');
    }

    public function updateProfile(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($admin->id)],
            'theme' => ['nullable', Rule::in(['light', 'dark'])],
        ]);
        $data['email'] = strtolower($data['email']);
        if (empty($data['theme'])) unset($data['theme']);
        $admin->update($data);

        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],
        ]);
        if (!Hash::check($data['current_password'], $admin->password)) {
            return back()->withErrors(['current_password' => 'Your current password is incorrect.']);
        }
        $admin->update(['password' => $data['password']]);
        $request->session()->regenerate();

        return back()->with('success', 'Password changed.');
    }
}
