<?php

namespace App\Services;

use App\Models\Service;
use App\Models\Setting;

/**
 * Used ONLY when n8n/Gemini is not configured or temporarily unreachable.
 * Every fact comes from the database (services, prices, durations, hours) so nothing is invented.
 * It does not create bookings; it points customers to the booking form instead.
 */
class ChatFallbackService
{
    public function __construct(private AvailabilityService $availability)
    {
    }

    public function reply(string $message): string
    {
        $text = mb_strtolower($message);
        $services = Service::active()->ordered()->get();

        $matched = $services->first(fn (Service $s) => str_contains($text, mb_strtolower($s->name))
            || str_contains($text, str_replace('-', ' ', $s->slug)) || ($s->slug === 'facial-treatment' && str_contains($text, 'facial'))
            || ($s->slug === 'body-massage' && str_contains($text, 'massage')) || ($s->slug === 'nail-care' && str_contains($text, 'nail')));

        if ($matched && preg_match('/how long|duration|take|minutes|hours long/', $text)) {
            return "{$matched->name} takes about {$matched->duration_label}. It starts at {$matched->price_label}.";
        }
        if ($matched) {
            return "{$matched->name} starts at {$matched->price_label} and takes about {$matched->duration_label}. {$matched->short_description} You can book it from the Book Now page.";
        }
        if (preg_match('/hour|open|close|schedule|time do you/', $text) && !preg_match('/available|slot/', $text)) {
            return $this->hoursSentence();
        }
        if (preg_match('/available|availability|slot|free time/', $text)) {
            return 'To see real-time availability, open the Book Now page, choose a service and date, and the open time slots will appear. '.$this->hoursSentence();
        }
        if (preg_match('/book|appointment|reserve|schedule an/', $text)) {
            return 'Yes! Use the Book Now button at the top of the page. Choose a service, date, and an open time. Your request stays Pending until the salon confirms it.';
        }
        if (preg_match('/service|offer|price|cost|how much|menu/', $text)) {
            $list = $services->map(fn ($s) => "{$s->name} ({$s->price_label}, {$s->duration_label})")->implode('; ');
            return "Our services: {$list}. Prices are starting prices.";
        }
        if (preg_match('/human|staff|person|talk to|contact|phone|email/', $text)) {
            return 'You can reach our team through the Contact page. Your message goes straight to the salon administrators.';
        }
        if (preg_match('/\b(hi|hello|hey|good (morning|afternoon|evening))\b/', $text)) {
            return 'Hello! Welcome to Blush & Bloom Salon. Ask me about our services, prices, durations, hours, or how to book.';
        }
        return 'I can help with our services, prices, durations, salon hours, and booking. For anything else, please send a message through the Contact page.';
    }

    private function hoursSentence(): string
    {
        $h = $this->availability->businessHours();
        $names = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $days = count($h['open_days']) === 7 ? 'daily' : implode(', ', array_map(fn ($d) => $names[$d], $h['open_days']));
        $sentence = 'Appointments are available '.$days.' from '.date('g:i A', strtotime($h['opening_time'])).' to '.date('g:i A', strtotime($h['closing_time'])).'.';
        return $h['is_sample'] ? $sentence.' (These are sample hours. Please confirm with the salon.)' : $sentence;
    }
}
