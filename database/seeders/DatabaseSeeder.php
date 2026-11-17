<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Inquiry;
use App\Models\Service;
use App\Models\Setting;
use App\Support\SalonDefaults;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Safe to run more than once: every insert uses firstOrCreate/updateOrCreate,
 * so existing salon records are kept.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAdmin();
        $this->seedServicesAndSettings();
        $this->seedSampleRecords();
    }

    private function seedAdmin(): void
    {
        $email = config('salon.admin_email');
        $password = config('salon.admin_password');
        $admin = Admin::where('email', $email)->first();

        if (!$admin) {
            if (!$password) {
                $password = Str::password(14, symbols: false);
                $this->command?->warn("SALON_ADMIN_PASSWORD is empty. Generated a one-time admin password: {$password}");
                $this->command?->warn('Write it down now, then change it in Admin → Settings.');
            }
            Admin::create(['name' => config('salon.admin_name'), 'email' => $email, 'password' => $password]);
            $this->command?->info("Administrator created: {$email}");
        } elseif ($password) {
            $admin->update(['password' => $password]);
            $this->command?->info("Administrator password updated from SALON_ADMIN_PASSWORD: {$email}");
        }
    }

    private function seedServicesAndSettings(): void
    {
        foreach (SalonDefaults::services() as $i => $service) {
            Service::firstOrCreate(['slug' => $service['slug']], $service + ['sort_order' => $i + 1, 'is_active' => true]);
        }
        foreach (SalonDefaults::settings() as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
        cache()->forget('salon.settings');
    }

    /** Clearly labelled demonstration records so the admin dashboard is not empty. */
    private function seedSampleRecords(): void
    {
        $customers = [
            ['name' => 'Maria Santos', 'email' => 'maria@example.com', 'phone' => '09171234567', 'status' => 'Active'],
            ['name' => 'John Reyes', 'email' => 'john@example.com', 'phone' => '09181234567', 'status' => 'Active'],
            ['name' => 'Angela Cruz', 'email' => 'angela@example.com', 'phone' => '09191234567', 'status' => 'Active'],
            ['name' => 'Mark Dela Cruz', 'email' => 'mark@example.com', 'phone' => '09201234567', 'status' => 'New'],
        ];
        foreach ($customers as $c) Customer::firstOrCreate(['email' => $c['email']], $c);

        $samples = [
            ['maria@example.com', 'haircut', 1, '10:00', 'Confirmed'],
            ['john@example.com', 'nail-care', 1, '13:00', 'Pending'],
            ['angela@example.com', 'hair-styling', 2, '15:00', 'Confirmed'],
            ['mark@example.com', 'facial-treatment', 3, '11:00', 'Pending'],
        ];
        foreach ($samples as [$email, $slug, $days, $time, $status]) {
            $customer = Customer::where('email', $email)->first();
            $service = Service::where('slug', $slug)->first();
            if (!$customer || !$service) continue;
            if (Appointment::where('customer_id', $customer->id)->where('notes', 'Sample booking')->where('service_id', $service->id)->exists()) continue;
            Appointment::create([
                'customer_id' => $customer->id, 'service_id' => $service->id, 'service' => $service->name,
                'appointment_date' => now()->addDays($days)->toDateString(), 'appointment_time' => $time,
                'duration_minutes' => $service->duration_minutes, 'status' => $status, 'notes' => 'Sample booking', 'source' => 'seeder',
            ]);
        }

        $inquiries = [
            ['Maria Santos', 'maria@example.com', 'Haircut pricing', 'How much is a haircut?', 'Resolved'],
            ['John Reyes', 'john@example.com', 'Appointment availability', 'Do you have slots tomorrow afternoon?', 'Pending'],
            ['Angela Cruz', 'angela@example.com', 'Available services', 'What services do you offer for weddings?', 'New'],
        ];
        foreach ($inquiries as [$name, $email, $subject, $message, $status]) {
            Inquiry::firstOrCreate(['email' => $email, 'subject' => $subject], compact('name', 'email', 'subject', 'message', 'status'));
        }
    }
}
