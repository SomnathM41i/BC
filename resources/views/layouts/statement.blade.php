<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <title>@yield('title', 'Contractor') · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto w-full max-w-3xl px-4 py-4">
            <p class="font-semibold text-slate-900">{{ config('app.name') }}</p>
            <p class="text-xs text-slate-500">Contractor statement</p>
        </div>
    </header>

    <main class="mx-auto w-full max-w-3xl px-4 py-6 md:py-8">
        @yield('content')
    </main>
</body>
</html>
