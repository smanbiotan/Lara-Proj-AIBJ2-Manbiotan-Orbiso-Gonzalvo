<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

/** Customer sign in / registration (customer guard only – never grants admin access). */
class CustomerAuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ]);
        $credentials['email'] = strtolower(trim($credentials['email']));

        $key = 'customer-login:'.Str::lower($credentials['email']).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['email' => 'Too many sign-in attempts. Please try again in '.RateLimiter::availableIn($key).' seconds.']);
        }

        if (!Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['email' => 'The email or password you entered is incorrect.']);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();
        Auth::guard('customer')->user()->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('account'))->with('success', 'Welcome back! You are now signed in.');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+()\-\s]{7,30}$/'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ], ['phone.regex' => 'Please enter a valid contact number.']);
        $data['email'] = strtolower(trim($data['email']));

        $customer = Customer::where('email', $data['email'])->first();
        if ($customer && $customer->hasAccount()) {
            throw ValidationException::withMessages(['email' => 'An account with this email already exists. Please sign in (or use Continue with Google) instead.']);
        }

        if ($customer) {
            // A guest who booked before can activate an account for the same email.
            $customer->update(['password' => $data['password'], 'phone' => $customer->phone ?: $data['phone'], 'status' => 'Active']);
        } else {
            $customer = Customer::create($data + ['status' => 'Active']);
        }

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        return redirect()->intended(route('account'))->with('success', 'Your account has been created. Welcome to Blush & Bloom Salon!');
    }

    public function account()
    {
        $customer = Auth::guard('customer')->user();
        $appointments = $customer->appointments()->with('serviceRecord')
            ->orderByDesc('appointment_date')->orderByDesc('appointment_time')->get();

        return view('auth.account', compact('customer', 'appointments'));
    }

    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        // Keep any separate admin session intact; just rotate the session and CSRF token.
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'You have been signed out.');
    }
}
