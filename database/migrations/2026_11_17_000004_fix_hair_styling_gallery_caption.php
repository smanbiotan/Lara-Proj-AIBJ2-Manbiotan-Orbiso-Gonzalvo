<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** Corrects one default gallery caption so it matches its photo. Admin-edited captions are untouched. */
return new class extends Migration {
    public function up(): void
    {
        foreach (DB::table('services')->select('id', 'gallery')->get() as $service) {
            $gallery = json_decode((string) $service->gallery, true);
            if (!is_array($gallery)) continue;
            $changed = false;
            foreach ($gallery as &$photo) {
                if (($photo['caption'] ?? null) === 'A soft finished style with a ribbon accent.') {
                    $photo['caption'] = 'Foil highlights for soft, sunlit colour.';
                    $changed = true;
                }
            }
            unset($photo);
            if ($changed) DB::table('services')->where('id', $service->id)->update(['gallery' => json_encode($gallery), 'updated_at' => now()]);
        }
    }

    public function down(): void {}
};
