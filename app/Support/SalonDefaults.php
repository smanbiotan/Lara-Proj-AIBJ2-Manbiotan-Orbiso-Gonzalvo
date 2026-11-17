<?php

namespace App\Support;

/**
 * Default demonstration content for Blush & Bloom Salon.
 *
 * These values are only used to fill an EMPTY database (migration backfill and seeder).
 * After installation, administrators manage services and settings from the Admin Portal,
 * and the public website reads everything from MySQL.
 *
 * Prices are demonstration prices. Contact details and hours were supplied by the group.
 */
class SalonDefaults
{
    public const CATEGORIES = [
        'hair' => 'Hair Services',
        'nails' => 'Nail Services',
        'skin' => 'Facial and Skin Care',
        'spa' => 'Spa and Massage',
        'makeup' => 'Makeup Services',
    ];

    public static function settings(): array
    {
        return [
            'business_name' => 'Blush & Bloom Salon',
            'business_tagline' => 'Your moment to bloom.',
            'business_email' => 'blushnbloom.salon@gmail.com',
            'business_phone' => '09414152352',
            'business_address' => 'Butuan City, Agusan del Norte, Philippines',
            'opening_time' => '08:00',
            'closing_time' => '18:00',
            'open_days' => '0,1,2,3,4,5,6',
            'slot_interval' => '30',
            'hours_are_sample' => '0',
            'reminder_hours_before' => '24',
        ];
    }

    public static function services(): array
    {
        return [
            [
                'name' => 'Haircut',
                'slug' => 'haircut',
                'category' => 'hair',
                'price' => 250,
                'duration_minutes' => 45,
                'short_description' => 'A fresh, precise cut shaped around your face, hair texture, and lifestyle.',
                'intro' => 'A tailored haircut begins with a short conversation about the length, shape, and finish you want. Your stylist then sections, cuts, and refines your hair with scissors and comb for a clean, easy-to-manage result.',
                'description' => 'Whether you need a simple trim, a refreshed shape, or a completely new everyday look, our haircut service focuses on precision and comfort. We consider your hair texture, growth pattern, and daily routine so your cut looks good long after you leave the salon.',
                'image' => 'images/services/haircut.jpg',
                'gallery' => [
                    ['image' => 'images/services/haircut-2.jpg', 'caption' => 'Sectioning and cutting with scissors and comb.'],
                    ['image' => 'images/services/haircut-3.jpg', 'caption' => 'A relaxing shampoo before your cut.'],
                ],
                'inclusions' => ['Consultation about your preferred length and style', 'Precision cut with scissors and comb', 'Light blow-dry and finishing', 'Simple at-home styling tips'],
                'benefits' => ['Removes split ends and keeps hair healthy', 'A shape that suits your face and routine', 'Easier everyday styling'],
                'ideal_for' => 'Anyone ready for a tidy-up, a refreshed shape, or a new everyday look.',
                'preparation' => 'Bring a reference photo if you have a specific cut in mind. Arrive with your hair as you normally wear it so your stylist can see its natural fall.',
                'aftercare' => 'Use a heat protectant when styling and book a trim every 6–8 weeks to keep your shape.',
                'faqs' => [
                    ['q' => 'Is washing included?', 'a' => 'A light finish is included. Ask your stylist if you would like a wash before your cut.'],
                    ['q' => 'Can I bring a photo?', 'a' => 'Yes, reference photos help your stylist understand the look you want.'],
                ],
            ],
            [
                'name' => 'Hair Styling',
                'slug' => 'hair-styling',
                'category' => 'hair',
                'price' => 500,
                'duration_minutes' => 60,
                'short_description' => 'Blowouts, curls, and polished waves for everyday moments and special occasions.',
                'intro' => 'From soft waves to a sleek blowout, this service shapes and finishes your hair for the occasion or look you want.',
                'description' => 'Our stylists curl, blow-dry, and style your hair to match your outfit, event, or mood. We agree on the finish together before we start, then build volume, movement, and shine that lasts through your day or evening.',
                'image' => 'images/services/hair-styling.jpg',
                'gallery' => [
                    ['image' => 'images/services/hair-styling-2.jpg', 'caption' => 'Foil highlights for soft, sunlit colour.'],
                    ['image' => 'images/services/hair-styling-3.jpg', 'caption' => 'Bright styling stations, ready for you.'],
                ],
                'inclusions' => ['Style consultation', 'Blow-dry, curls, waves, or sleek finish', 'Heat protection', 'Finishing spray or serum'],
                'benefits' => ['A polished look for photos and events', 'Longer-lasting hold', 'Professional volume and shine'],
                'ideal_for' => 'Celebrations, events, photoshoots, date nights, or simply a more polished day.',
                'preparation' => 'Come with clean, dry hair if possible and share inspiration photos of the style you would like.',
                'aftercare' => 'Avoid humidity and tight hats to help your style last. Sleep on a silk pillowcase to keep curls longer.',
                'faqs' => [
                    ['q' => 'How long will my curls last?', 'a' => 'Usually through the day or evening, depending on your hair type and the weather.'],
                    ['q' => 'Can you style short hair?', 'a' => 'Yes. Your stylist will recommend options that suit your length.'],
                ],
            ],
            [
                'name' => 'Nail Care',
                'slug' => 'nail-care',
                'category' => 'nails',
                'price' => 350,
                'duration_minutes' => 45,
                'short_description' => 'Shaping, cuticle care, and a clean polish finish for neat, healthy-looking nails.',
                'intro' => 'A feel-good nail refresh with careful attention to shape and finish. Choose a natural look or your favourite polish shade.',
                'description' => 'Our nail technicians gently shape, buff, and tidy your nails and cuticles before applying a polish or natural finish. It is a calm, detail-focused service that leaves your hands looking cared for.',
                'image' => 'images/services/nail-care.jpg',
                'gallery' => [
                    ['image' => 'images/services/nail-care-2.jpg', 'caption' => 'A neat, soft-pink manicure.'],
                    ['image' => 'images/services/nail-care-3.jpg', 'caption' => 'Choose from a wall of polish shades.'],
                ],
                'inclusions' => ['Nail shaping and buffing', 'Gentle cuticle care', 'Polish or natural finish', 'Hand moisturiser'],
                'benefits' => ['Clean, even nail shape', 'Healthier-looking cuticles', 'A finished look for any occasion'],
                'ideal_for' => 'Regular maintenance, a quick self-care break, or a polished finish before an event.',
                'preparation' => 'Remove old gel polish beforehand if possible, and mention any nail sensitivities.',
                'aftercare' => 'Wear gloves when cleaning and apply cuticle oil daily to keep your polish looking fresh.',
                'faqs' => [
                    ['q' => 'Can I choose my polish colour?', 'a' => 'Yes, choose from the shades available on the day of your visit.'],
                    ['q' => 'How long does polish last?', 'a' => 'Regular polish typically lasts several days, depending on daily activity.'],
                ],
            ],
            [
                'name' => 'Facial Treatment',
                'slug' => 'facial-treatment',
                'category' => 'skin',
                'price' => 700,
                'duration_minutes' => 60,
                'short_description' => 'A calming facial with cleansing, a nourishing mask, and moisturising care.',
                'intro' => 'Set aside a quiet moment for a gentle facial focused on cleansing, comfort, and a refreshed-feeling finish.',
                'description' => 'Your esthetician begins with a short skin preference check, then cleanses, applies a soothing mask, and finishes with moisturiser. This is a relaxing cosmetic service, not a medical treatment.',
                'image' => 'images/services/facial-treatment.jpg',
                'gallery' => [
                    ['image' => 'images/services/facial-treatment-2.jpg', 'caption' => 'Applying a nourishing mask with a soft brush.'],
                    ['image' => 'images/services/facial-treatment-3.jpg', 'caption' => 'Time to relax while the mask works.'],
                ],
                'inclusions' => ['Skin preference check', 'Gentle cleansing', 'Relaxing face mask', 'Moisturising and finishing care'],
                'benefits' => ['Skin feels fresh and clean', 'A calm, relaxing break', 'Comfortable, soft finish'],
                'ideal_for' => 'Anyone looking for a relaxing skincare pause and a simple refresh.',
                'preparation' => 'Tell your esthetician about allergies, sensitivities, or products you prefer to avoid. Arrive without heavy makeup if possible.',
                'aftercare' => 'Avoid harsh exfoliants for 24 hours and use sunscreen when you go outdoors.',
                'faqs' => [
                    ['q' => 'Is this a medical facial?', 'a' => 'No. It is a relaxing cosmetic service. Please consult a doctor for skin conditions.'],
                    ['q' => 'Can I wear makeup afterwards?', 'a' => 'We recommend letting your skin breathe for a few hours.'],
                ],
            ],
            [
                'name' => 'Body Massage',
                'slug' => 'body-massage',
                'category' => 'spa',
                'price' => 800,
                'duration_minutes' => 60,
                'short_description' => 'A relaxing, full-body massage that helps you slow down and unwind.',
                'intro' => 'A calming, relaxation-focused massage designed to give you a comfortable pause from a busy day.',
                'description' => 'After a quick comfort and pressure check-in, your therapist delivers a soothing massage, adjusting pressure throughout the session so you stay comfortable and relaxed.',
                'image' => 'images/services/body-massage.jpg',
                'gallery' => [
                    ['image' => 'images/services/body-massage-2.jpg', 'caption' => 'Warm stones to melt away tension.'],
                    ['image' => 'images/services/body-massage-3.jpg', 'caption' => 'Hot stone therapy for deep relaxation.'],
                ],
                'inclusions' => ['Comfort and pressure check-in', 'Relaxing full-body massage', 'Pressure adjusted to your comfort', 'Quiet rest time afterwards'],
                'benefits' => ['Helps you relax and unwind', 'Eases everyday tension', 'A peaceful break from routine'],
                'ideal_for' => 'Guests who want a quiet wellness break and a relaxing salon experience.',
                'preparation' => 'Let your therapist know about areas to avoid, sensitivities, or health considerations. Avoid a heavy meal right before your session.',
                'aftercare' => 'Drink water, rest, and avoid strenuous activity for the rest of the day.',
                'faqs' => [
                    ['q' => 'Can I choose the pressure?', 'a' => 'Yes. Tell your therapist at any time if you want lighter or firmer pressure.'],
                    ['q' => 'Is this suitable during pregnancy?', 'a' => 'Please contact the salon first so we can advise on suitable options.'],
                ],
            ],
            [
                'name' => 'Makeup',
                'slug' => 'makeup',
                'category' => 'makeup',
                'price' => 600,
                'duration_minutes' => 60,
                'short_description' => 'A personalised makeup look, from soft and natural to defined evening glam.',
                'intro' => 'A makeup look built around your occasion and preferred finish, from fresh and natural to a more defined evening style.',
                'description' => 'Your makeup artist discusses the occasion, outfit, and look you want, then prepares your skin and applies complexion, eye, and lip makeup with careful blending for a polished result.',
                'image' => 'images/services/makeup.jpg',
                'gallery' => [
                    ['image' => 'images/services/makeup-2.jpg', 'caption' => 'Blending for a soft, natural glow.'],
                    ['image' => 'images/services/makeup-3.jpg', 'caption' => 'Finishing touches for a polished look.'],
                ],
                'inclusions' => ['Look consultation', 'Skin preparation', 'Complexion, eye, and lip makeup', 'Final blending and setting'],
                'benefits' => ['Camera-ready finish', 'A look tailored to your features', 'Long-wearing application'],
                'ideal_for' => 'Parties, celebrations, photoshoots, special events, or whenever you want a polished look.',
                'preparation' => 'Arrive with a clean, moisturised face. Bring inspiration photos and mention any product sensitivities.',
                'aftercare' => 'Remove makeup gently with a cleanser before bed and moisturise afterwards.',
                'faqs' => [
                    ['q' => 'Do you provide false lashes?', 'a' => 'Ask the salon about lash options available on the day of your appointment.'],
                    ['q' => 'Can I do a trial?', 'a' => 'Contact the salon to arrange a trial before a major event.'],
                ],
            ],
        ];
    }
}
