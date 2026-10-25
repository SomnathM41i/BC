<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name')) · {{ config('app.name') }}</title>
    <meta name="description" content="@yield('meta_description', 'Construction and interior work, managed properly.')">
    <meta name="theme-color" content="#1c1917">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-stone-50 text-stone-800 antialiased">
    @include('partials.site.nav')

    <main>
        @yield('content')
    </main>

    @include('partials.site.footer')
    @stack('scripts')
</body>
</html>
