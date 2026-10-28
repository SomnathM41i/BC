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
            'mr' => 'तुमच्या स्वप्नातील घरासाठी आम्ही काळजीपूर्वक बांधकाम करतो.',
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
        // Coordinates come from the owner-supplied Google Maps listing.
        'latitude' => 17.3126691,
        'longitude' => 74.8801869,
        'map_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3809.059686608882!2d74.8801869!3d17.3126691!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bc1535a1001f10f%3A0x2ab4d598c53b07c9!2sBandgar%20Construction%20%26%20Developer%27s%20Kargani!5e0!3m2!1sen!2sin!4v1793137999778!5m2!1sen!2sin',
        'map_link' => 'https://www.google.com/maps/search/?api=1&query=Bandgar%20Construction%20%26%20Developer%27s%20Kargani',
    ],

    // Confirmed social profiles only. Example: ['facebook' => 'https://...'].
    'social' => [],

    /*
     | Client reviews. Leave this list empty until a customer has agreed in
     | writing to have their words published; the testimonial section on the
     | home page is hidden while the list is empty. Each entry:
     | ['quote' => '...', 'name' => '...', 'context' => 'Home in Atpadi']
     */
    'testimonials' => [],

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
        'interior-design',
    ],

    /*
     | Services shown on a project page, chosen by the project's category.
     | Keys must exist in the services list above.
     */
    'portfolio_related_services' => [
        'residential' => ['architectural-design', '2d-plan-design', 'residential-construction'],
        'modern-homes' => ['architectural-design', '3d-design', 'residential-construction'],
        'architectural-design' => ['architectural-design', '2d-plan-design', '3d-design'],
        'interior-design' => ['interior-design', '3d-design', 'estimation-planning'],
    ],

    'seo' => [
        'title_suffix' => 'Bandgar Construction & Developers',
        'default_description' => 'Residential construction, architectural planning, structural and interior design services in Kargani, Shetfale Road, Taluka Atpadi, Sangli District, Maharashtra, India.',
        'og_image' => 'images/site/portfolio/modern-duplex-residence/cover.jpg',
        'theme_color' => '#64151F',
    ],

    'contact' => [
        // Where new enquiries are emailed. Falls back to site.email when null.
        'notify_email' => env('SITE_NOTIFY_EMAIL'),
        // Requests per minute per IP for the enquiry form.
        'throttle' => '5,1',
    ],

    'payment_tracking' => [
        // How long a verified public session may view its own records.
        'session_minutes' => 30,
        // Lookup limits: per IP + mobile number, and per IP overall.
        'lookup_per_minute' => 5,
        'lookup_per_hour' => 20,
    ],

];
