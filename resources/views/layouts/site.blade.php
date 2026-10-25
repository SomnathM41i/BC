@use('App\Support\Site\SiteRoutes')
@php
    // Inline @section content arrives HTML-escaped by Blade; decode it so the
    // layout escapes exactly once when printing.
    $pageTitle = trim(html_entity_decode($__env->yieldContent('title'), ENT_QUOTES)) ?: __('site.meta.home.title');
    $pageDescription = trim(html_entity_decode($__env->yieldContent('description'), ENT_QUOTES)) ?: __('site.meta.home.description');
    $canonical = url()->current();
    $ogImage = asset(config('site.seo.og_image'));

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'HomeAndConstructionBusiness',
        'name' => $site['name'],
        'url' => SiteRoutes::url('home'),
        'image' => $ogImage,
        'description' => __('site.meta.home.description'),
        'telephone' => $site['phones'][0]['tel'] ?? null,
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => $site['address_line'],
            'addressLocality' => 'Atpadi',
            'addressRegion' => 'Maharashtra',
            'addressCountry' => 'IN',
        ],
        'areaServed' => ['Atpadi', 'Sangli', 'Maharashtra'],
    ];

    if ($site['social'] !== []) {
        $schema['sameAs'] = array_values($site['social']);
    }
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $pageTitle }} | {{ config('site.seo.title_suffix') }}</title>
    <meta name="description" content="{{ $pageDescription }}">
    <link rel="canonical" href="{{ $canonical }}">

    <link rel="alternate" hreflang="en" href="{{ SiteRoutes::switchUrl('en') }}">
    <link rel="alternate" hreflang="mr" href="{{ SiteRoutes::switchUrl('mr') }}">
    <link rel="alternate" hreflang="x-default" href="{{ SiteRoutes::switchUrl('en') }}">

    <meta name="theme-color" content="{{ config('site.seo.theme_color') }}">
    <meta name="robots" content="@yield('robots', 'index, follow')">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('site.seo.title_suffix') }}">
    <meta property="og:locale" content="{{ app()->getLocale() === 'mr' ? 'mr_IN' : 'en_IN' }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Manrope:wght@400;500;600;700&family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Noto+Serif+Devanagari:wght@500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')

    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
</head>
<body class="min-h-screen bg-sand font-site text-ink antialiased">
    <a href="#main"
       class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-full focus:bg-brand focus:px-5 focus:py-3 focus:text-sm focus:font-semibold focus:text-white">
        {{ __('site.nav.skip') }}
    </a>

    @include('partials.site.nav')

    <main id="main">
        @yield('content')
    </main>

    @include('partials.site.footer')

    @stack('scripts')
</body>
</html>
