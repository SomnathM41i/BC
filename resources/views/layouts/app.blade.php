<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
@php($user = auth()->user())
<div class="flex min-h-screen">
    <aside class="hidden w-64 flex-col bg-slate-900 text-slate-200 md:flex">
        <div class="border-b border-slate-700 px-6 py-5">
            <p class="text-lg font-semibold text-white">Bandgar</p>
            <p class="text-xs text-slate-400">Account Manager</p>
        </div>
        <nav class="flex-1 space-y-1 px-3 py-4 text-sm">
            @include('partials.nav-links', ['user' => $user, 'dark' => true])
        </nav>
        <div class="border-t border-slate-700 px-6 py-4 text-xs">
            <p class="font-medium text-white">{{ $user->name }}</p>
            <p class="mb-3 text-slate-400">{{ $user->role->label() }}</p>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-slate-700 px-3 py-1.5 text-white hover:bg-slate-600">
                    <x-icon name="logout" />
                    Sign out
                </button>
            </form>
        </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="sticky top-0 z-30 flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3 md:hidden">
            <span class="font-semibold">Bandgar Account Manager</span>
            <details class="group relative">
                <summary class="flex cursor-pointer list-none items-center gap-1.5 rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-700 [&::-webkit-details-marker]:hidden">
                    <x-icon name="menu" />
                    Menu
                </summary>
                <div class="absolute right-0 z-40 mt-2 w-60 rounded-md border border-slate-200 bg-white p-1.5 text-sm shadow-lg">
                    <div class="border-b border-slate-100 px-3 py-2 text-xs">
                        <p class="font-medium text-slate-900">{{ $user->name }}</p>
                        <p class="text-slate-500">{{ $user->role->label() }}</p>
                    </div>
                    @include('partials.nav-links', ['user' => $user, 'dark' => false])
                    <form method="POST" action="{{ route('admin.logout') }}" class="mt-1 border-t border-slate-100 pt-1">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-left text-sm text-red-600 hover:bg-slate-100">
                            <x-icon name="logout" />
                            Sign out
                        </button>
                    </form>
                </div>
            </details>
        </header>

        <main class="flex-1 p-4 md:p-8">
            @if (session('status'))
                <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="list-inside list-disc space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
