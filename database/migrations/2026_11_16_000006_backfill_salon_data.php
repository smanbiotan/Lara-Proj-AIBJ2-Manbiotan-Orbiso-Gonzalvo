<?php

use App\Support\SalonDefaults;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Data upgrade for databases created by the earlier version of this project.
 * - Inserts the default service menu and settings ONLY when missing.
 * - Links existing appointments to services by name, adds references and durations,
 *   and normalises "10:00 AM" style times to 24-hour "10:00".
 * - Replaces old brand names on the administrator display name.
 * Existing records are kept.
 */
return new class extends Migration {
    public function up(): void
    {
        $now = now();

        foreach (SalonDefaults::services() as $index => $service) {
            if (DB::table('services')->where('slug', $service['slug'])->exists()) continue;
            DB::table('services')->insert([
                'name' => $service['name'], 'slug' => $service['slug'], 'category' => $service['category'],
                'short_description' => $service['short_description'], 'intro' => $service['intro'],
                'description' => $service['description'], 'price' => $service['price'],
                'duration_minutes' => $service['duration_minutes'], 'image' => $service['image'],
                'gallery' => json_encode($service['gallery']), 'inclusions' => json_encode($service['inclusions']),
                'benefits' => json_encode($service['benefits']), 'ideal_for' => $service['ideal_for'],
                'preparation' => $service['preparation'], 'aftercare' => $service['aftercare'],
                'faqs' => json_encode($service['faqs']), 'is_active' => true, 'sort_order' => $index + 1,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        foreach (SalonDefaults::settings() as $key => $value) {
            if (!DB::table('settings')->where('key', $key)->exists()) {
                DB::table('settings')->insert(['key' => $key, 'value' => $value, 'created_at' => $now, 'updated_at' => $now]);
            }
        }

        $services = DB::table('services')->get()->keyBy(fn ($s) => strtolower($s->name));
        DB::table('appointments')->orderBy('id')->get()->each(function ($a) use ($services) {
            $update = [];
            $service = $services[strtolower((string) $a->service)] ?? null;
            if (!$a->service_id && $service) {
                $update['service_id'] = $service->id;
                $update['duration_minutes'] = $service->duration_minutes;
            }
            if (!$a->reference) {
                do { $ref = 'BB-'.strtoupper(Str::random(6)); } while (DB::table('appointments')->where('reference', $ref)->exists());
                $update['reference'] = $ref;
            }
            $time = strtotime((string) $a->appointment_time);
            if ($time !== false && date('H:i', $time) !== $a->appointment_time) {
                $update['appointment_time'] = date('H:i', $time);
            }
            if ($update) DB::table('appointments')->where('id', $a->id)->update($update);
        });

        DB::table('admins')->whereIn('name', ['GlowUp Studio', 'GlowUp Beauty Studio', 'BizFlow AI', 'Cura Beauty Salon'])
            ->update(['name' => 'Blush & Bloom Salon Admin', 'updated_at' => $now]);

        // Settings are cached; make sure the website reads the freshly inserted values.
        cache()->forget('salon.settings');
    }

    public function down(): void
    {
        // Data backfill is not reversed so salon records are never lost.
    }
};
