<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * "Continue with Google" for customer accounts (OAuth 2.0 authorization-code flow).
 * Uses Laravel's HTTP client, so no extra Composer package is needed.
 * Requires GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET in .env (never in the ZIP or the browser).
 */
class GoogleAuthController extends Controller
{
    private const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const USERINFO_URL = 'https://openidconnect.googleapis.com/v1/userinfo';

    public static function enabled(): bool
    {
        return filled(config('salon.google.client_id')) && filled(config('salon.google.client_secret'));
    }

    public function redirect(Request $request)
    {
        if (!self::enabled()) {
            return redirect()->route('login')->withErrors(['email' => 'Google sign-in is not set up yet. Please sign in with your email or create an account.']);
        }

        $state = Str::random(40);
        $request->session()->put('google_oauth_state', $state);

        return redirect()->away(self::AUTH_URL.'?'.http_build_query([
            'client_id' => config('salon.google.client_id'),
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'prompt' => 'select_account',
        ]));
    }

    public function callback(Request $request)
    {
        $expected = $request->session()->pull('google_oauth_state');
        $failed = fn (string $msg = 'Google sign-in did not complete. Please try again or use your email.') =>
            redirect()->route('login')->withErrors(['email' => $msg]);

        if ($request->filled('error')) return $failed('Google sign-in was cancelled.');
        if (!self::enabled() || !$expected || !hash_equals($expected, (string) $request->query('state')) || !$request->filled('code')) {
            return $failed();
        }

        try {
            $token = Http::asForm()->timeout(15)->post(self::TOKEN_URL, [
                'code' => $request->query('code'),
                'client_id' => config('salon.google.client_id'),
                'client_secret' => config('salon.google.client_secret'),
                'redirect_uri' => $this->redirectUri(),
                'grant_type' => 'authorization_code',
            ]);
            if (!$token->successful() || !$token->json('access_token')) {
                Log::warning('Google token exchange failed', ['status' => $token->status()]);
                return $failed();
            }

            $profile = Http::withToken($token->json('access_token'))->timeout(15)->get(self::USERINFO_URL);
            if (!$profile->successful()) return $failed();
        } catch (\Throwable $e) {
            Log::warning('Google sign-in error', ['message' => $e->getMessage()]);
            return $failed('We could not reach Google right now. Please try again or use your email.');
        }

        $googleId = (string) $profile->json('sub');
        $email = strtolower(trim((string) $profile->json('email')));
        if ($googleId === '' || $email === '' || $profile->json('email_verified') !== true) {
            return $failed('Your Google account needs a verified email address.');
        }

        $customer = Customer::where('google_id', $googleId)->first() ?? Customer::where('email', $email)->first();
        if ($customer) {
            $customer->forceFill([
                'google_id' => $customer->google_id ?: $googleId,
                'avatar' => $profile->json('picture') ?: $customer->avatar,
                'status' => $customer->status === 'Inactive' ? 'Inactive' : 'Active',
            ])->save();
        } else {
            $customer = Customer::create([
                'name' => $profile->json('name') ?: Str::before($email, '@'),
                'email' => $email,
                'google_id' => $googleId,
                'avatar' => $profile->json('picture'),
                'status' => 'Active',
            ]);
        }

        Auth::guard('customer')->login($customer, true);
        $request->session()->regenerate();
        $customer->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('account'))->with('success', 'Signed in with Google. Welcome, '.$customer->name.'!');
    }

    private function redirectUri(): string
    {
        return config('salon.google.redirect') ?: route('google.callback');
    }
}
