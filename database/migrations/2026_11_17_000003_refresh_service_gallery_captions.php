<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The default photos were replaced with real photographs, so a few default gallery captions
 * are updated to describe the new pictures. Only captions that still have their ORIGINAL
 * default text are changed; anything an admin edited is left alone.
 */
return new class extends Migration {
    private array $captions = [
        "Refining the length for a clean, even finish." => "A relaxing shampoo before your cut.",
        "Shaping curls with a curling iron." => "A soft finished style with a ribbon accent.",
        "Soft, glossy waves as a finishing touch." => "Bright styling stations, ready for you.",
        "Careful polish application." => "A neat, soft-pink manicure.",
        "A neat, glossy finish." => "Choose from a wall of polish shades.",
        "Gentle, flowing massage techniques." => "Warm stones to melt away tension.",
        "A quiet room designed for relaxation." => "Hot stone therapy for deep relaxation.",
        "Eye makeup detail for a finished look." => "Finishing touches for a polished look.",
    ];

    public function up(): void
    {
        foreach (DB::table('services')->select('id', 'gallery')->get() as $service) {
            $gallery = json_decode((string) $service->gallery, true);
            if (!is_array($gallery)) continue;
            $changed = false;
            foreach ($gallery as &$photo) {
                $caption = $photo['caption'] ?? null;
                if ($caption !== null && isset($this->captions[$caption])) {
                    $photo['caption'] = $this->captions[$caption];
                    $changed = true;
                }
            }
            unset($photo);
            if ($changed) DB::table('services')->where('id', $service->id)->update(['gallery' => json_encode($gallery), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        // Captions are content; nothing to roll back.
    }
};
