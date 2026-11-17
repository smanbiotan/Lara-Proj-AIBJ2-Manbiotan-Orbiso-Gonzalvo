<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SalonController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer website — Blush & Bloom Salon
|--------------------------------------------------------------------------
| Header: Home | About | Salon | Contact      Sign In | Book Now
*/

// Member 1 – Sherwin Alvin Manbiotan: Home page
Route::get('/', [PageController::class, 'home'])->name('home');

// Member 2 – Gerald EJ Gonzalvo: About page
// (Member 2 adds the About page route here)

// Member 3 – Anthony Josh Orbiso: Contact page
Route::get('/contact', [PageController::class, 'contact'])
    ->name('contact');

Route::post('/contact', [PageController::class, 'contactStore'])
    ->middleware('throttle:6,1')
    ->name('contact.store');

// Salon service catalogue and service detail pages
Route::get('/salon', [SalonController::class, 'index'])->name('salon.index');
Route::get('/salon/{service:slug}', [SalonController::class, 'show'])->name('salon.show');
// Old links from the previous version keep working.
Route::permanentRedirect('/services', '/salon');
Route::get('/services/{slug}', fn (string $slug) => redirect()->route('salon.show', $slug, 301));

// Appointment booking (works for guests and signed-in customers; does not depend on n8n)
Route::get('/booking', [BookingController::class, 'create'])->name('booking');
Route::post('/booking', [BookingController::class, 'store'])->middleware('throttle:10,1')->name('booking.store');
// Guests sign in (or sign up) after filling in the form, then review and confirm here.
Route::middleware('auth:customer')->group(function () {
    Route::get('/booking/review', [BookingController::class, 'review'])->name('booking.review');
    Route::post('/booking/confirm', [BookingController::class, 'confirm'])->middleware('throttle:10,1')->name('booking.confirm');
});
Route::get('/booking/confirmed/{reference}', [BookingController::class, 'confirmed'])->name('booking.confirmed');

// Customer authentication (customer guard)
Route::middleware('guest:customer')->group(function () {
    Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [CustomerAuthController::class, 'login'])->name('login.store');
    Route::get('/register', [CustomerAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [CustomerAuthController::class, 'register'])->middleware('throttle:6,1')->name('register.store');
    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->middleware('throttle:20,1')->name('google.callback');
});
Route::middleware('auth:customer')->group(function () {
    Route::get('/account', [CustomerAuthController::class, 'account'])->name('account');
    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| Administrator portal (separate admin guard, never linked from the public header)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [Admin\AuthController::class, 'show'])->name('login');
    Route::post('/login', [Admin\AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.store');

    Route::middleware('admin')->group(function () {
        Route::redirect('/', '/admin/dashboard');
        Route::get('/dashboard', Admin\DashboardController::class)->name('dashboard');

        Route::resource('customers', Admin\CustomerController::class);

        Route::get('/appointments', [Admin\AppointmentController::class, 'index'])->name('appointments.index');
        Route::get('/appointments/{appointment}', [Admin\AppointmentController::class, 'show'])->name('appointments.show');
        Route::patch('/appointments/{appointment}/status', [Admin\AppointmentController::class, 'updateStatus'])->name('appointments.status');
        Route::patch('/appointments/{appointment}/reschedule', [Admin\AppointmentController::class, 'reschedule'])->name('appointments.reschedule');

        Route::get('/inquiries', [Admin\InquiryController::class, 'index'])->name('inquiries.index');
        Route::get('/inquiries/{inquiry}', [Admin\InquiryController::class, 'show'])->name('inquiries.show');
        Route::patch('/inquiries/{inquiry}', [Admin\InquiryController::class, 'update'])->name('inquiries.update');

        Route::get('/services', [Admin\ServiceController::class, 'index'])->name('services.index');
        Route::get('/services/create', [Admin\ServiceController::class, 'create'])->name('services.create');
        Route::post('/services', [Admin\ServiceController::class, 'store'])->name('services.store');
        Route::get('/services/{service:id}/edit', [Admin\ServiceController::class, 'edit'])->name('services.edit');
        Route::put('/services/{service:id}', [Admin\ServiceController::class, 'update'])->name('services.update');
        Route::patch('/services/{service:id}/toggle', [Admin\ServiceController::class, 'toggle'])->name('services.toggle');

        Route::get('/automation-logs', [Admin\AutomationLogController::class, 'index'])->name('automation-logs');

        Route::get('/settings', [Admin\SettingsController::class, 'edit'])->name('settings');
        Route::put('/settings/business', [Admin\SettingsController::class, 'updateBusiness'])->name('settings.business');
        Route::put('/settings/profile', [Admin\SettingsController::class, 'updateProfile'])->name('settings.profile');
        Route::patch('/settings/theme', [Admin\SettingsController::class, 'updateTheme'])->name('settings.theme');
        Route::get('/profile', [Admin\SettingsController::class, 'profile'])->name('profile');
        Route::put('/settings/password', [Admin\SettingsController::class, 'updatePassword'])->name('settings.password');

        Route::post('/logout', [Admin\AuthController::class, 'logout'])->name('logout');
    });
});
