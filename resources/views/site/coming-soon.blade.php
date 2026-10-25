<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="index,follow">
    <title>Bandgar Construction — Coming soon</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 text-stone-800 antialiased">
    <main class="mx-auto flex min-h-screen w-full max-w-3xl flex-col justify-center px-6 py-16">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-700">Bandgar Construction</p>

        <h1 class="mt-4 text-3xl font-semibold tracking-tight text-stone-900 sm:text-4xl">
            Our new website is on the way.
        </h1>

        <p class="mt-4 max-w-xl text-base leading-relaxed text-stone-600">
            We are putting together a proper home for the work we do, the projects we have
            delivered and the people we build for. Please check back shortly.
        </p>

        <div class="mt-10 border-t border-stone-200 pt-6">
            <a href="{{ route('admin.login') }}"
               class="text-sm font-medium text-stone-600 underline decoration-stone-300 underline-offset-4 hover:text-stone-900">
                Staff sign in
            </a>
        </div>
    </main>
</body>
</html>
