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
| email address, social profile or map link until it has been confirmed.
|
*/

return [

    'company' => [
        'names' => [
            'en' => 'Bandgar Construction & Developers',
            'mr' => 'बंदगर कन्स्ट्रक्शन अँड डेव्हलपर्स',
        ],
        'short_names' => [
            'en' => 'Bandgar',
            'mr' => 'बंदगर',
        ],
        'taglines' => [
            'en' => 'We build your dream home with care.',
            'mr' => 'तुमच्या स्वप्नातील घर आम्ही काळजीपूर्वक उभारतो.',
        ],
    ],

    /*
    | Phone numbers exactly as printed on the company signboard.
    | "name_mr" is the Marathi transliteration used on the Marathi site.
    */
    'phones' => [
        ['name' => 'Er. Bharat Bandgar', 'name_mr' => 'अर्ज. भरत बंदगर', 'number' => '9021113887'],
        ['name' => 'Shri. Sanjorao Bandgar', 'name_mr' => 'श्री. संजोराव बंदगर', 'number' => '9420450672'],
        ['name' => 'Shri. Vishal Bandgar', 'name_mr' => 'श्री. विशाल बंदगर', 'number' => '7387291539'],
    ],

    // WhatsApp number in international format without "+" (used for wa.me links).
    'whatsapp' => '919021113887',

    // Not confirmed yet. Fill in to enable enquiry notification emails.
    'email' => env('SITE_EMAIL'),

    'address' => [
        'lines' => [
            'en' => [
                'Kargani, Shetphule Road',
                'Gaothan No. 2',
                'Taluka Atpadi, District Sangli',
                'Maharashtra, India',
            ],
            'mr' => [
                'कारगणी, शेतफुळे रोड',
                'गावठाण क्र. २',
                'ता. आटपाडी, जि. सांगली',
                'महाराष्ट्र, भारत',
            ],
        ],
        // Set these once the exact office pin is confirmed.
        'latitude' => null,
        'longitude' => null,
        'map_embed_url' => null,
        'map_link' => null,
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
        'default_description' => 'Residential construction, architectural planning, structural and interior design services in Atpadi, Sangli, Maharashtra.',
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
