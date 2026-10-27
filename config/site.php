<?php

/*
|--------------------------------------------------------------------------
| Public website configuration
|--------------------------------------------------------------------------
|
| Everything the public site shows about the business lives here so that
| phone numbers, the address and SEO defaults can be updated in one place.
|
| After changing any value run:  php artisan config:clear
| (and php artisan config:cache if you cache configuration in production).
|
| Only verified business information belongs in this file. Do not add an
| email address or social profile until it has been confirmed. Map links
| should use the verified address unless an exact office pin is confirmed.
|
*/

return [

    'company' => [
        'logo' => 'images/site/brand-logo.png',
        // Trimmed, transparent lockup without the tagline, sized for the header.
        'logo_header' => 'images/site/brand-logo-header.png',
        'names' => [
            'en' => 'Bandgar Construction & Developers',
            'mr' => 'Bandgar Construction & Developers',
        ],
        'short_names' => [
            'en' => 'Bandgar',
            'mr' => 'Bandgar',
        ],
        'taglines' => [
            'en' => 'We build your dream home with care.',
            'mr' => 'तुमच्या Dream Home साठी आम्ही काळजीपूर्वक Construction करतो.',
        ],
    ],

    /*
    | Phone numbers exactly as printed on the company signboard.
    | "name_mr" is the Marathi transliteration used on the Marathi site.
    */
    'phones' => [
        ['name' => 'Er. Bharat Bandgar', 'name_mr' => 'इंजि. भरत बंडगर', 'number' => '9021113887'],
    ],

    // WhatsApp number in international format without "+" (used for wa.me links).
    'whatsapp' => '919021113887',

    // Not confirmed yet. Fill in to enable enquiry notification emails.
    'email' => env('SITE_EMAIL'),

    'address' => [
        // One-line address for the compact bar at the top of the site.
        'short' => [
            'en' => 'Kargani, Shetfale Road, Taluka Atpadi, Sangli',
            'mr' => 'करगणी, शेटफळे रोड, ता. आटपाडी, जि. सांगली',
        ],
        // Shorter still, for phone-width screens.
        'compact' => [
            'en' => 'Kargani, Atpadi',
            'mr' => 'करगणी, आटपाडी',
        ],
        'lines' => [
            'en' => [
                'Kargani, Shetfale Road',
                'Gav Number 2',
                'Taluka Atpadi, Sangli District',
                'Maharashtra, India',
            ],
            'mr' => [
                'करगणी, शेटफळे रोड',
                'ता. आटपाडी, जि. सांगली',
                'महाराष्ट्र, भारत',
            ],
        ],
        // Coordinates remain unset because an exact office pin is not verified.
        'latitude' => null,
        'longitude' => null,
        'map_embed_url' => null,
        'map_link' => 'https://www.google.com/maps/search/?api=1&query=Kargani%2C+Shetfale+Road%2C+Gav+Number+2%2C+Taluka+Atpadi%2C+Sangli+District%2C+Maharashtra%2C+India',
    ],

    // Confirmed social profiles only. Example: ['facebook' => 'https://...'].
    'social' => [],

    /*
     | Service keys. Names, descriptions and benefits are translated in
     | lang/{en,mr}/site.php; the order here is the order shown on the site.
     */
    'services' => [
        'architectural-design',
        '2d-plan-design',
        '3d-design',
        'structural-design',
        'residential-construction',
        'interior-design',
        'landscaping-design',
        'electrical-design',
        'plumbing-design',
        'construction-supervision',
        'contracting',
        'estimation-planning',
        'video-vr-animation',
    ],

    /*
     | Public portfolio categories. The keys are stored in the database and
     | translated for display; labels live in lang/{en,mr}/site.php.
     */
    'portfolio_categories' => [
        'residential',
        'modern-homes',
        'architectural-design',
    ],

    'seo' => [
        'title_suffix' => 'Bandgar Construction & Developers',
        'default_description' => 'Residential construction, architectural planning, structural and interior design services in Kargani, Shetfale Road, Gav Number 2, Taluka Atpadi, Sangli District, Maharashtra, India.',
        'og_image' => 'images/site/photos/hero.jpg',
        'theme_color' => '#64151F',
    ],

    'contact' => [
        // Where new enquiries are emailed. Falls back to site.email when null.
        'notify_email' => env('SITE_NOTIFY_EMAIL'),
        // Requests per minute per IP for the enquiry form.
        'throttle' => '5,1',
    ],

];
