<header class="border-b border-stone-200 bg-stone-50/90 backdrop-blur">
    <div class="mx-auto flex w-full max-w-6xl items-center justify-between px-4 py-4 md:px-8">
        <a href="{{ url('/') }}" class="flex items-baseline gap-2">
            <span class="text-lg font-semibold tracking-tight text-stone-900">{{ config('app.name') }}</span>
            <span class="hidden text-xs uppercase tracking-widest text-amber-700 sm:inline">Construction</span>
        </a>

        <a href="{{ route('admin.login') }}"
           class="rounded-md border border-stone-300 px-3 py-1.5 text-sm text-stone-600 transition hover:border-stone-400 hover:text-stone-900">
            Staff sign in
        </a>
    </div>
</header>
