<?php

namespace App\Support;

/**
 * Real photographs for the default salon images.
 *
 * Each built-in image path is mapped to a free photo from Unsplash (Unsplash License:
 * free for commercial and non-commercial use, hotlinking from images.unsplash.com is allowed).
 * The visitor's browser loads them directly from Unsplash's CDN.
 *
 * Photos uploaded by admins (public/uploads/...) are never replaced.
 * Set SALON_REMOTE_IMAGES=false in .env to go back to the local files in public/images.
 */
class SalonImages
{
    /** local path => [Unsplash photo id, photographer] */
    private const PHOTOS = [
        // Hero slider
        'images/hero/hero-haircut.jpg' => ['1634480258143-ffb5e6df0029', 'Hair Spies'],
        'images/hero/hero-styling.jpg' => ['1580618672591-eb180b1a973f', 'Adam Winger'],
        'images/hero/hero-facial.jpg' => ['1570172619644-dfd03ed5d881', 'Rosa Rafael'],
        'images/hero/hero-nails.jpg' => ['1632345031435-8727f6897d53', 'Giorgio Trovato'],
        'images/hero/hero-massage.jpg' => ['1519823551278-64ac92734fb1', 'Toa Heftiba'],

        // About page (bright salon interior)
        'images/about/salon.jpg' => ['1633681926035-ec1ac984418a', 'Benyamin Bohlouli'],

        // Team portraits (stock models; the salon's staff names are fictional)
        'images/team/bianca.jpg' => ['1565793244233-3d09028aad47', 'Mathilda Khoo'],
        'images/team/camille.jpg' => ['1621130668430-e7794ba3d9dd', 'Alwin Thomas'],
        'images/team/dana.jpg' => ['1597349789235-c8e2c85880d1', 'Solen Feyissa'],
        'images/team/elena.jpg' => ['1595380785068-8608441faa8e', 'tabitha turner'],
        'images/team/francesca.jpg' => ['1550957400-e84b24b9eaee', 'Henk Mohabier'],

        // Haircut
        'images/services/haircut.jpg' => ['1634480258143-ffb5e6df0029', 'Hair Spies'],
        'images/services/haircut-2.jpg' => ['1634449571010-02389ed0f9b0', 'Lindsay Cash'],
        'images/services/haircut-3.jpg' => ['1595476108010-b4d1f102b1b1', 'Adam Winger'],
        // Hair styling
        'images/services/hair-styling.jpg' => ['1580618672591-eb180b1a973f', 'Adam Winger'],
        'images/services/hair-styling-2.jpg' => ['1605980625982-b128a7e7fde2', 'Lera Kogan'],
        'images/services/hair-styling-3.jpg' => ['1633681926035-ec1ac984418a', 'Benyamin Bohlouli'],
        // Nail care
        'images/services/nail-care.jpg' => ['1632345031435-8727f6897d53', 'Giorgio Trovato'],
        'images/services/nail-care-2.jpg' => ['1610992015762-45dca7fa3a85', 'Unsplash contributor'],
        'images/services/nail-care-3.jpg' => ['1619607146034-5a05296c8f9a', 'Unsplash contributor'],
        // Facial treatment
        'images/services/facial-treatment.jpg' => ['1570172619644-dfd03ed5d881', 'Rosa Rafael'],
        'images/services/facial-treatment-2.jpg' => ['1761718210089-ba3bb5ccb54f', 'kimia kazemi'],
        'images/services/facial-treatment-3.jpg' => ['1616394584738-fc6e612e71b9', 'engin akyurt'],
        // Body massage
        'images/services/body-massage.jpg' => ['1519823551278-64ac92734fb1', 'Toa Heftiba'],
        'images/services/body-massage-2.jpg' => ['1600334089648-b0d9d3028eb2', 'engin akyurt'],
        'images/services/body-massage-3.jpg' => ['1696841212541-449ca29397cc', 'Taylor Heery'],
        // Makeup
        'images/services/makeup.jpg' => ['1709477542149-f4e0e21d590b', 'Lola Azizada'],
        'images/services/makeup-2.jpg' => ['1636023730877-233b9237d4ec', 'Alena Plotnikova'],
        'images/services/makeup-3.jpg' => ['1709477542170-f11ee7d471a0', 'Lola Azizada'],
    ];

    public static function url(?string $path, int $width = 1400, ?int $height = null): string
    {
        $path = ltrim((string) $path, '/');
        if ($path === '') $path = 'images/services/haircut.jpg';
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) return $path;

        if (config('salon.remote_images', true) && isset(self::PHOTOS[$path])) {
            $size = $height ? '&w='.$width.'&h='.$height.'&crop=faces' : '&w='.$width;
            return 'https://images.unsplash.com/photo-'.self::PHOTOS[$path][0].'?auto=format&fit=crop'.$size.'&q=80';
        }

        return is_file(public_path($path)) ? asset($path) : '';
    }

    /** Photographer credits for the README / About page. */
    public static function credits(): array
    {
        return array_values(array_unique(array_map(fn ($p) => $p[1], self::PHOTOS)));
    }
}
