<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Publishes the salon's official contact details and opening hours
 * (supplied by the group). Updates or inserts setting rows only — no other data is touched.
 * Admins can still change these later in Admin → Settings.
 */
return new class extends Migration {
    private array $values = [
        'business_email' => 'blushnbloom.salon@gmail.com',
        'business_phone' => '09414152352',
        'business_address' => 'Butuan City, Agusan del Norte, Philippines',
        'opening_time' => '08:00',
        'closing_time' => '18:00',
        'hours_are_sample' => '0',
    ];

    public function up(): void
    {
        $now = now();
        foreach ($this->values as $key => $value) {
            if (DB::table('settings')->where('key', $key)->exists()) {
                DB::table('settings')->where('key', $key)->update(['value' => $value, 'updated_at' => $now]);
            } else {
                DB::table('settings')->insert(['key' => $key, 'value' => $value, 'created_at' => $now, 'updated_at' => $now]);
            }
        }
        Cache::forget('salon.settings');
    }

    public function down(): void
    {
        // Contact details are business data; rolling back the code does not erase them.
    }
};
