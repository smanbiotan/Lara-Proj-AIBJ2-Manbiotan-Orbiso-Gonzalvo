<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Appointment;
use App\Models\AutomationLog;
use App\Models\Customer;
use App\Models\Inquiry;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SalonTest extends TestCase
{
    use RefreshDatabase;

    private string $date;

    protected function setUp(): void
    {
        parent::setUp();
        $this->date = now()->addDays(2)->toDateString();
    }

    private function bookingData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Customer', 'email' => 'test@example.com', 'phone' => '09171112222',
            'service' => 'haircut', 'appointment_date' => $this->date, 'appointment_time' => '10:00', 'notes' => 'Hello',
        ], $overrides);
    }

    /**
     * Full customer booking: fill in the form as a guest, sign in, then confirm.
     * Returns the response of the last step (or the validation response if step 1 failed).
     */
    private function book(array $overrides = [])
    {
        $data = $this->bookingData($overrides);
        $first = $this->post('/booking', $data);
        if (session()->has('errors')) return $first;

        $customer = Customer::firstOrCreate(['email' => strtolower($data['email'])], ['name' => $data['name'], 'phone' => $data['phone'], 'status' => 'Active']);
        $response = $this->actingAs($customer, 'customer')->post('/booking/confirm');
        auth('customer')->logout();
        return $response;
    }

    public function test_public_pages_load_with_correct_branding_and_header(): void
    {
        foreach (['/', '/about', '/salon', '/contact', '/booking', '/login', '/register', '/salon/haircut'] as $url) {
            $response = $this->get($url)->assertOk()->assertSee('Blush &amp; Bloom', false);
            $html = $response->getContent();
            foreach (['BizFlow', 'GlowUp', 'Cura Beauty'] as $old) $this->assertStringNotContainsString($old, $html, "$old found on $url");
        }
        $home = $this->get('/')->getContent();
        preg_match('/<header class="site-header".*?<\/header>/s', $home, $m);
        $header = $m[0];
        foreach (['Home', 'About', 'Salon', 'Contact', 'Sign In', 'Book Now'] as $label) $this->assertStringContainsString(">$label<", $header);
        $this->assertStringNotContainsStringIgnoringCase('search', $header);
        $this->assertStringNotContainsString('/admin', $header);
        $this->assertStringContainsString('Sherwin Alvin Manbiotan', $home);
        $this->assertStringContainsString('Gerald EJ Gonzalvo', $home);
        $this->assertStringContainsString('Anthony Josh Orbiso', $home);
    }

    public function test_school_assets_are_pushed_per_page(): void
    {
        $this->get('/')->assertSee('css/home.css')->assertSee('js/home.js');
        $this->get('/about')->assertSee('css/about.css')->assertSee('js/about.js');
        $this->get('/contact')->assertSee('css/contact.css')->assertSee('js/contact.js');
    }

    public function test_salon_shows_six_database_services_and_prices_follow_database(): void
    {
        $this->assertSame(6, Service::count());
        $page = $this->get('/salon')->assertOk();
        foreach (['Haircut' => '₱250', 'Hair Styling' => '₱500', 'Nail Care' => '₱350', 'Facial Treatment' => '₱700', 'Body Massage' => '₱800', 'Makeup' => '₱600'] as $name => $price) {
            $page->assertSee($name)->assertSee($price);
        }
        Service::where('slug', 'haircut')->update(['price' => 275]);
        $this->get('/salon')->assertSee('₱275')->assertDontSee('₱250');
        Service::where('slug', 'makeup')->update(['is_active' => false]);
        $this->get('/salon/makeup')->assertNotFound();
    }

    public function test_haircut_and_hair_styling_use_different_photos(): void
    {
        $this->assertNotEquals(Service::where('slug', 'haircut')->value('image'), Service::where('slug', 'hair-styling')->value('image'));
    }

    public function test_book_this_service_preselects_service(): void
    {
        $html = $this->get('/booking?service=facial-treatment')->getContent();
        $this->assertMatchesRegularExpression('/value="facial-treatment"[^>]*checked/', $html);
        $this->assertDoesNotMatchRegularExpression('/value="haircut"[^>]*checked/', $html);
    }

    public function test_guest_booking_creates_pending_appointment_with_reference(): void
    {
        $response = $this->book();
        $appointment = Appointment::first();
        $this->assertNotNull($appointment);
        $response->assertRedirect(route('booking.confirmed', $appointment->reference));
        $this->assertSame('Pending', $appointment->status);
        $this->assertMatchesRegularExpression('/^BB-\d{6}-[A-Z0-9]{4}$/', $appointment->reference);
        $this->assertSame(45, $appointment->duration_minutes);
        $this->get(route('booking.confirmed', $appointment->reference))->assertOk()->assertSee($appointment->reference);
        // n8n not configured: booking still works, event logged as skipped
        $this->assertSame('skipped', AutomationLog::first()->status);
    }

    public function test_confirmation_page_is_private(): void
    {
        $this->book();
        $ref = Appointment::first()->reference;
        $this->flushSession();
        $this->get(route('booking.confirmed', $ref))->assertNotFound();
    }

    public function test_booking_rejects_past_dates_conflicts_and_closed_hours(): void
    {
        $this->book(['appointment_date' => now()->subDay()->toDateString()])->assertSessionHasErrors('appointment_date');
        $this->book(['appointment_time' => '07:00'])->assertSessionHasErrors('appointment_time');

        $this->book()->assertRedirect();
        // Hair Styling shares the hair station and overlaps 10:00–10:45 → conflict
        $this->book(['email' => 'b@example.com', 'service' => 'hair-styling', 'appointment_time' => '10:30'])->assertSessionHasErrors('appointment_time');
        // Nail Care uses a different station → allowed at the same time
        $this->book(['email' => 'c@example.com', 'service' => 'nail-care', 'appointment_time' => '10:00'])->assertSessionHasNoErrors();
        $this->assertSame(2, Appointment::count());
    }

    public function test_duplicate_submission_token_creates_only_one_booking(): void
    {
        $this->book(['submission_token' => 'same-token-123']);
        $this->book(['submission_token' => 'same-token-123']);
        $this->assertSame(1, Appointment::count());
    }

    public function test_existing_customer_details_are_not_overwritten_by_public_booking(): void
    {
        Customer::create(['name' => 'Original Name', 'email' => 'test@example.com', 'phone' => '0999', 'status' => 'Active']);
        $this->book(['name' => 'Someone Else']);
        $this->assertSame('Original Name', Customer::where('email', 'test@example.com')->value('name'));
        $this->assertSame(1, Customer::count());
    }

    public function test_availability_api_marks_booked_slots(): void
    {
        $this->book();
        $json = $this->getJson("/api/appointments/availability?service=haircut&date={$this->date}")->assertOk()->json();
        $slot = collect($json['slots'])->firstWhere('time', '10:00');
        $this->assertFalse($slot['available']);
        $this->assertTrue(collect($json['slots'])->firstWhere('time', '11:00')['available']);
        $this->getJson('/api/appointments/availability?service=haircut&date=2000-01-01')->assertStatus(422);
    }

    public function test_contact_form_saves_inquiry_json_and_validates(): void
    {
        $this->postJson('/contact', ['name' => '', 'email' => 'bad', 'subject' => '', 'message' => ''])->assertStatus(422)->assertJsonValidationErrors(['name', 'email', 'subject', 'message']);
        $this->postJson('/contact', ['name' => 'Ana', 'email' => 'ana@example.com', 'subject' => 'Question', 'message' => 'Do you offer bridal makeup?'])->assertOk()->assertJsonStructure(['message']);
        $this->assertSame('New', Inquiry::first()->status);
    }

    public function test_customer_register_login_and_account(): void
    {
        $this->post('/register', ['name' => 'Ana', 'email' => 'ana@example.com', 'phone' => '09170000000', 'password' => 'secret123', 'password_confirmation' => 'secret123'])->assertRedirect('/account');
        $this->assertAuthenticated('customer');
        $this->assertNotSame('secret123', Customer::first()->password);
        $this->get('/account')->assertOk()->assertSee('Ana');
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest('customer');
        $this->post('/login', ['email' => 'ana@example.com', 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->post('/login', ['email' => 'ana@example.com', 'password' => 'secret123'])->assertRedirect('/account');
    }

    public function test_guest_must_sign_in_before_booking_is_created(): void
    {
        $this->post('/booking', $this->bookingData())->assertRedirect(route('booking', ['step' => 'sign-in']));
        $this->assertSame(0, Appointment::count());
        $this->assertSame(0, Customer::count());
        $this->assertSame('haircut', session('booking_draft.service'));

        // The sign-in dialog is shown with Google + email options, and the details are kept.
        $html = $this->get(route('booking', ['step' => 'sign-in']))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/id="authGate"\s+data-open="true"/', $html);
        $this->assertStringContainsString('Continue with Google', $html);
        $this->assertStringContainsString('value="Test Customer"', $html);

        // Review page needs a signed-in customer.
        $this->get('/booking/review')->assertRedirect('/login');
    }

    public function test_guest_signs_up_then_reviews_and_confirms(): void
    {
        $this->post('/booking', $this->bookingData());
        $this->post('/register', ['name' => 'Ana Cruz', 'email' => 'ana@example.com', 'phone' => '09170000000', 'password' => 'secret123', 'password_confirmation' => 'secret123'])
            ->assertRedirect(route('booking.review'));
        $this->get('/booking/review')->assertOk()->assertSee('Confirm your appointment')->assertSee('Haircut')->assertSee('ana@example.com');

        $response = $this->post('/booking/confirm');
        $appointment = Appointment::first();
        $response->assertRedirect(route('booking.confirmed', $appointment->reference));
        $this->assertSame('ana@example.com', $appointment->customer->email);
        $this->assertNull(session('booking_draft'));
        $this->get(route('booking.confirmed', $appointment->reference))->assertOk();
    }

    public function test_guest_signs_in_and_returns_to_review(): void
    {
        Customer::create(['name' => 'Ana', 'email' => 'ana@example.com', 'password' => 'secret123', 'status' => 'Active']);
        $this->post('/booking', $this->bookingData());
        $this->post('/login', ['email' => 'ana@example.com', 'password' => 'secret123'])->assertRedirect(route('booking.review'));
    }

    public function test_guest_sees_taken_time_before_signing_in(): void
    {
        $this->book();
        $this->post('/booking', $this->bookingData(['email' => 'late@example.com']))->assertSessionHasErrors('appointment_time');
        $this->assertNull(session('booking_draft'));
    }

    public function test_signed_in_customer_books_directly_with_account_email(): void
    {
        $customer = Customer::create(['name' => 'Ana', 'email' => 'ana@example.com', 'password' => 'secret123', 'status' => 'Active']);
        $this->actingAs($customer, 'customer')->post('/booking', $this->bookingData(['email' => 'someone@else.com']))->assertRedirect();
        $this->assertSame($customer->id, Appointment::first()->customer_id);
    }

    public function test_booking_page_has_calendar_and_header_theme_switch(): void
    {
        $html = $this->get('/booking')->assertOk()->getContent();
        $this->assertStringContainsString('id="bookingCalendar"', $html);
        $this->assertStringContainsString('data-min="'.now()->toDateString().'"', $html);
        // Theme switch sits right after (to the right of) the Book Now button in the header.
        $this->assertMatchesRegularExpression('/<a class="btn btn-primary" href="[^"]*\/booking">Book Now<\/a>\s*(\{\{--.*?--\}\}\s*)?<button class="theme-switch" id="themeToggle"/s', $html);
    }

    public function test_google_sign_in_hidden_until_configured_and_creates_customer(): void
    {
        config(['salon.google.client_id' => null, 'salon.google.client_secret' => null]);
        $this->get('/auth/google')->assertRedirect('/login');

        config(['salon.google.client_id' => 'cid', 'salon.google.client_secret' => 'secret']);
        $redirect = $this->get('/auth/google');
        $this->assertStringStartsWith('https://accounts.google.com/o/oauth2/v2/auth?', $redirect->headers->get('Location'));

        Http::fake([
            'oauth2.googleapis.com/*' => Http::response(['access_token' => 'tok']),
            'openidconnect.googleapis.com/*' => Http::response(['sub' => 'g-123', 'email' => 'Gina@Example.com', 'email_verified' => true, 'name' => 'Gina Lopez']),
        ]);
        $this->get('/auth/google/callback?state=wrong&code=abc')->assertRedirect('/login');
        $this->assertGuest('customer');

        $this->get('/auth/google');
        $state = session('google_oauth_state');
        $this->get('/auth/google/callback?state='.$state.'&code=abc')->assertRedirect('/account');
        $customer = Customer::where('email', 'gina@example.com')->first();
        $this->assertSame('g-123', $customer->google_id);
        $this->assertTrue($customer->hasAccount());
        $this->assertAuthenticatedAs($customer, 'customer');
    }

    public function test_contact_details_section_testimonials_and_real_photos(): void
    {
        $html = $this->get('/')->getContent();
        foreach (['blushnbloom.salon@gmail.com', '09414152352', 'Butuan City, Agusan del Norte, Philippines', '8:00 AM', 'Section AIBJ2', 'Rated 5 out of 5 stars'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        $this->assertStringContainsString('images.unsplash.com/photo-', $html);
        $this->assertStringNotContainsString('CSFV2', $html);
    }

    public function test_customers_cannot_access_admin(): void
    {
        $customer = Customer::create(['name' => 'C', 'email' => 'c@example.com', 'password' => 'secret123', 'status' => 'Active']);
        $this->actingAs($customer, 'customer');
        foreach (['/admin/dashboard', '/admin/customers', '/admin/appointments', '/admin/services', '/admin/settings', '/admin/automation-logs'] as $url) {
            $this->get($url)->assertRedirect('/admin/login');
        }
        // A customer's credentials do not work on the admin login.
        $this->post('/admin/login', ['email' => 'c@example.com', 'password' => 'secret123'])->assertSessionHasErrors('email');
    }

    public function test_admin_can_login_and_use_every_page(): void
    {
        $admin = Admin::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'AdminPass123']);
        $this->post('/admin/login', ['email' => 'admin@example.com', 'password' => 'AdminPass123'])->assertRedirect('/admin/dashboard');
        $this->book();
        $appointment = Appointment::first();
        $inquiry = Inquiry::create(['name' => 'X', 'email' => 'x@example.com', 'subject' => 'S', 'message' => 'M', 'status' => 'New']);
        $serviceId = \App\Models\Service::orderBy('id')->value('id');
        foreach (['/admin/dashboard', '/admin/customers', '/admin/customers/create', '/admin/customers/'.$appointment->customer_id, '/admin/appointments', '/admin/appointments/'.$appointment->id,
                     '/admin/inquiries', '/admin/inquiries/'.$inquiry->id, '/admin/services', '/admin/services/create', '/admin/services/'.$serviceId.'/edit', '/admin/automation-logs', '/admin/settings'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->get('/admin/dashboard')->assertRedirect('/admin/login');
    }

    public function test_admin_login_is_rate_limited(): void
    {
        Admin::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'AdminPass123']);
        for ($i = 0; $i < 5; $i++) $this->post('/admin/login', ['email' => 'admin@example.com', 'password' => 'nope']);
        $this->post('/admin/login', ['email' => 'admin@example.com', 'password' => 'AdminPass123'])->assertSessionHasErrors('email');
        $this->assertGuest('admin');
    }

    public function test_admin_status_changes_send_events_once_and_protect_slots(): void
    {
        config(['salon.n8n.appointment_webhook_url' => 'https://n8n.test/webhook/appointments']);
        Http::fake(['n8n.test/*' => Http::response(['ok' => true])]);
        $admin = Admin::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'AdminPass123']);

        $this->book();
        $a = Appointment::first();
        $this->actingAs($admin, 'admin');
        $this->patch("/admin/appointments/{$a->id}/status", ['status' => 'Confirmed'])->assertRedirect();
        $this->patch("/admin/appointments/{$a->id}/status", ['status' => 'Confirmed']);
        $this->assertSame('Confirmed', $a->fresh()->status);

        Http::assertSent(fn ($r) => $r->hasHeader('X-Webhook-Secret', 'test-secret') && $r['event'] === 'appointment.created');
        $this->assertSame(1, AutomationLog::where('event', 'appointment.status_changed')->count());
        $this->assertSame('sent_to_n8n', AutomationLog::where('event', 'appointment.status_changed')->value('status'));

        // Cancel, let someone else take the slot, then re-confirming must fail.
        $this->patch("/admin/appointments/{$a->id}/status", ['status' => 'Cancelled']);
        $this->book(['email' => 'other@example.com'])->assertSessionHasNoErrors();
        $this->patch("/admin/appointments/{$a->id}/status", ['status' => 'Confirmed'])->assertSessionHasErrors('status');
        $this->assertSame('Cancelled', $a->fresh()->status);
        $this->patch("/admin/appointments/{$a->id}/status", ['status' => 'Bogus'])->assertSessionHasErrors('status');
    }

    public function test_n8n_down_does_not_break_booking(): void
    {
        config(['salon.n8n.appointment_webhook_url' => 'https://n8n.test/webhook/appointments']);
        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('down'));
        $this->book()->assertRedirect();
        $this->assertSame(1, Appointment::count());
        $this->assertSame('failed', AutomationLog::first()->status);
    }

    public function test_admin_can_manage_services_and_customers(): void
    {
        $admin = Admin::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'AdminPass123']);
        $this->actingAs($admin, 'admin');
        $this->post('/admin/services', ['name' => 'Hair Spa', 'category' => 'hair', 'short_description' => 'Nourishing treatment', 'price' => 900, 'duration_minutes' => 60, 'is_active' => 1,
            'inclusions' => "Wash\nMask", 'faqs' => 'Is it relaxing? | Yes, very.'])->assertRedirect('/admin/services');
        $spa = Service::where('slug', 'hair-spa')->first();
        $this->assertSame(['Wash', 'Mask'], $spa->inclusions);
        $this->assertSame([['q' => 'Is it relaxing?', 'a' => 'Yes, very.']], $spa->faqs);
        $this->get('/salon/hair-spa')->assertOk()->assertSee('₱900');
        $this->patch("/admin/services/{$spa->id}/toggle");
        $this->assertFalse($spa->fresh()->is_active);

        $this->post('/admin/customers', ['name' => 'New C', 'email' => 'newc@example.com', 'phone' => '0917 000 0000', 'status' => 'Active'])->assertRedirect();
        $c = Customer::where('email', 'newc@example.com')->first();
        $this->put("/admin/customers/{$c->id}", ['name' => 'Renamed', 'email' => 'newc@example.com', 'status' => 'Inactive'])->assertRedirect();
        $this->assertSame('Renamed', $c->fresh()->name);
        $this->get('/admin/customers?q=Renamed')->assertSee('Renamed');
        $this->delete("/admin/customers/{$c->id}");
        $this->assertNull(Customer::find($c->id));
    }

    public function test_n8n_tool_endpoints_require_token(): void
    {
        $this->getJson('/api/n8n/services')->assertUnauthorized();
        $this->getJson('/api/n8n/services', ['Authorization' => 'Bearer wrong'])->assertUnauthorized();
        $h = ['Authorization' => 'Bearer test-token-1234567890abcdef'];
        $this->getJson('/api/n8n/services', $h)->assertOk()->assertJsonCount(6, 'services');
        $this->getJson('/api/n8n/business-info', $h)->assertOk()->assertJsonPath('business_name', 'Blush & Bloom Salon');
        $this->getJson("/api/n8n/availability?service=Facial Treatment&date={$this->date}", $h)->assertOk()->assertJsonPath('service_slug', 'facial-treatment');
    }

    public function test_chatbot_tool_booking_goes_through_laravel_validation(): void
    {
        $h = ['Authorization' => 'Bearer test-token-1234567890abcdef'];
        $res = $this->postJson('/api/n8n/appointments', ['name' => 'Bot User', 'email' => 'BOT@example.com', 'phone' => '09171234567', 'service' => 'Body Massage', 'appointment_date' => $this->date, 'appointment_time' => '2:00 PM'], $h)->assertCreated();
        $ref = $res->json('reference');
        $this->assertSame('chatbot', Appointment::first()->source);
        // Same slot again → rejected by Laravel
        $this->postJson('/api/n8n/appointments', ['name' => 'Bot 2', 'email' => 'b2@example.com', 'phone' => '09171234567', 'service' => 'body-massage', 'appointment_date' => $this->date, 'appointment_time' => '14:00'], $h)
            ->assertStatus(422)->assertJsonPath('created', false);
        // Status lookup needs matching email
        $this->postJson('/api/n8n/appointments/status', ['reference' => $ref, 'email' => 'wrong@example.com'], $h)->assertNotFound();
        $this->postJson('/api/n8n/appointments/status', ['reference' => $ref, 'email' => 'bot@example.com'], $h)->assertOk()->assertJsonPath('status', 'Pending');
        $this->postJson('/api/n8n/escalations', ['name' => 'Bot User', 'email' => 'bot@example.com', 'message' => 'Please call me'], $h)->assertCreated();
        $this->assertSame('chatbot', Inquiry::first()->source);
    }

    public function test_reminders_are_claimed_once_and_results_recorded(): void
    {
        $h = ['Authorization' => 'Bearer test-token-1234567890abcdef'];
        $this->book(['appointment_date' => now()->addDay()->toDateString(), 'appointment_time' => '11:00']);
        $this->book(['email' => 'p@example.com', 'service' => 'makeup', 'appointment_date' => now()->addDay()->toDateString(), 'appointment_time' => '11:00']);
        $confirmed = Appointment::orderBy('id')->first();
        $confirmed->update(['status' => 'Confirmed']); // the second stays Pending → excluded

        $first = $this->postJson('/api/n8n/reminders/claim', ['hours_before' => 48], $h)->assertOk()->assertJsonPath('count', 1);
        $this->postJson('/api/n8n/reminders/claim', ['hours_before' => 48], $h)->assertJsonPath('count', 0);

        $eventId = $first->json('appointments.0.event_id');
        $this->postJson('/api/n8n/automation-results', ['event_id' => $eventId, 'success' => true], $h)->assertOk();
        $this->assertNotNull($confirmed->fresh()->reminder_sent_at);
        $this->assertSame('email_sent', AutomationLog::where('event_id', $eventId)->value('status'));
    }

    public function test_chat_uses_n8n_reply_or_database_fallback(): void
    {
        $this->postJson('/api/chat', ['message' => 'How much is a haircut?', 'session_id' => 'abcdefabcdefabcdef12'])->assertOk()
            ->assertJsonPath('mode', 'fallback')->assertJsonFragment(['session_id' => 'abcdefabcdefabcdef12']);
        $this->assertStringContainsString('₱250', $this->postJson('/api/chat', ['message' => 'How much is a haircut?'])->json('reply'));

        config(['salon.n8n.chat_webhook_url' => 'https://n8n.test/webhook/chat']);
        Http::fake(['n8n.test/*' => Http::response(['reply' => 'Hello from Gemini'])]);
        $this->postJson('/api/chat', ['message' => 'Hi', 'session_id' => 'abcdefabcdefabcdef12'])->assertJsonPath('reply', 'Hello from Gemini')->assertJsonPath('mode', 'ai');
        Http::assertSent(fn ($r) => $r['session_id'] === 'abcdefabcdefabcdef12' && count($r['history']) === 2 && $r->hasHeader('X-Webhook-Secret'));
    }

    public function test_dark_mode_is_the_default_theme(): void
    {
        // Customer website: dark unless the visitor saved "light" (localStorage).
        $this->assertStringContainsString('<html lang="en" data-theme="dark">', $this->get('/')->getContent());

        // Admin portal: new admins start in dark mode, and can still choose light.
        $admin = Admin::create(['name' => 'Dark Admin', 'email' => 'dark@example.com', 'password' => 'secret-pass-123']);
        $this->assertSame('dark', $admin->fresh()->theme);
        $this->actingAs($admin, 'admin');
        $this->assertStringContainsString('data-theme="dark"', $this->get('/admin/dashboard')->getContent());
        $admin->update(['theme' => 'light']);
        $this->assertStringContainsString('data-theme="light"', $this->get('/admin/dashboard')->getContent());
    }

    public function test_admin_account_menu_profile_and_theme_switch(): void
    {
        $admin = Admin::create(['name' => 'Blush & Bloom Salon Admin', 'email' => 'admin@example.com', 'password' => 'AdminPass123']);
        $this->actingAs($admin, 'admin');

        $html = $this->get('/admin/dashboard')->assertOk()->getContent();
        $this->assertStringContainsString('id="accountMenuBtn"', $html);
        $this->assertStringContainsString('data-theme-choice="dark"', $html);
        $this->assertStringContainsString('>BA<', $html);

        $this->get('/admin/profile')->assertOk()->assertSee('Your details')->assertSee('Upcoming appointments')->assertSee('Change password')->assertSee('admin@example.com');
        $this->assertStringNotContainsString('<span class="nav-icon"><svg class="ai-icon " viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M4 21v-7', $html);

        $this->patchJson('/admin/settings/theme', ['theme' => 'dark'])->assertOk()->assertJson(['theme' => 'dark']);
        $this->assertSame('dark', $admin->fresh()->theme);
        $this->assertStringContainsString('data-theme="dark"', $this->get('/admin/settings')->getContent());
        $this->patchJson('/admin/settings/theme', ['theme' => 'neon'])->assertStatus(422);

        // Profile can be saved without a theme field.
        $this->put('/admin/settings/profile', ['name' => 'Salon Admin', 'email' => 'admin@example.com'])->assertSessionHasNoErrors();
        $this->assertSame('dark', $admin->fresh()->theme);
    }

    public function test_blank_contact_settings_fall_back_to_official_details(): void
    {
        \App\Models\Setting::put(['business_email' => '', 'business_phone' => '', 'business_address' => '']);
        $html = $this->get('/contact')->getContent();
        $this->assertStringContainsString('blushnbloom.salon@gmail.com', $html);
        $this->assertStringContainsString('09414152352', $html);
        $this->assertStringNotContainsString('To be announced', $html);
    }
}
