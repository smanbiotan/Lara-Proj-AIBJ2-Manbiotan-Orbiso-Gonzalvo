<?php

namespace App\Models;

use App\Support\SalonDefaults;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/** Simple key/value business settings managed from Admin → Settings. */
class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function allValues(): array
    {
        return Cache::rememberForever('salon.settings', function () {
            $stored = Schema::hasTable('settings') ? static::pluck('value', 'key')->all() : [];
            $values = array_merge(SalonDefaults::settings(), $stored);
            // Never show a blank contact detail on the website: fall back to the salon's official details.
            foreach (['business_email', 'business_phone', 'business_address'] as $key) {
                if (trim((string) ($values[$key] ?? '')) === '') $values[$key] = SalonDefaults::settings()[$key];
            }
            return $values;
        });
    }

    public static function get(string $key, $default = null)
    {
        return static::allValues()[$key] ?? $default;
    }

    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        Cache::forget('salon.settings');
    }
}
