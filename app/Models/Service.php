<?php

namespace App\Models;

use App\Support\SalonDefaults;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name', 'slug', 'category', 'short_description', 'intro', 'description', 'price',
        'duration_minutes', 'image', 'gallery', 'inclusions', 'benefits', 'ideal_for',
        'preparation', 'aftercare', 'faqs', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2', 'is_active' => 'boolean', 'gallery' => 'array', 'inclusions' => 'array',
            'benefits' => 'array', 'faqs' => 'array', 'duration_minutes' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getCategoryLabelAttribute(): string
    {
        return SalonDefaults::CATEGORIES[$this->category] ?? ucfirst($this->category);
    }

    public function getImageUrlAttribute(): string
    {
        return \App\Support\SalonImages::url($this->image ?: 'images/services/haircut.jpg');
    }

    public function getPriceLabelAttribute(): string
    {
        return '₱'.number_format((float) $this->price, 0);
    }

    public function getDurationLabelAttribute(): string
    {
        $m = (int) $this->duration_minutes;
        if ($m < 60) return "{$m} minutes";
        $h = intdiv($m, 60);
        $rest = $m % 60;
        return $h.' '.($h === 1 ? 'hour' : 'hours').($rest ? " {$rest} min" : '');
    }

    /** All slideshow images: the main photo followed by gallery photos. */
    public function getSlidesAttribute(): array
    {
        $slides = [['url' => $this->image_url, 'caption' => $this->short_description]];
        foreach ($this->gallery ?? [] as $photo) {
            if (!empty($photo['image'])) {
                $slides[] = ['url' => \App\Support\SalonImages::url($photo['image']), 'caption' => $photo['caption'] ?? $this->name];
            }
        }
        return $slides;
    }

    /** Safe data the chatbot and public API may share. */
    public function toPublicArray(): array
    {
        return [
            'name' => $this->name, 'slug' => $this->slug, 'category' => $this->category_label,
            'description' => $this->short_description, 'starting_price' => (float) $this->price,
            'currency' => 'PHP', 'price_label' => $this->price_label, 'duration_minutes' => $this->duration_minutes,
            'duration_label' => $this->duration_label, 'url' => route('salon.show', $this),
        ];
    }
}
