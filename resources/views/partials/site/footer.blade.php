@use('App\Support\Site\SiteRoutes')

<footer class="relative mt-20 overflow-hidden bg-brand-dark text-white">
    <div class="absolute inset-0 bg-grid opacity-30" aria-hidden="true"></div>

    <div class="site-container relative grid gap-12 py-16 md:grid-cols-2 lg:grid-cols-4">
        <div class="lg:pr-6">
            <div class="flex items-center gap-3">
                <img src="{{ $site['logo'] }}" alt="{{ $site['name'] }} logo"
                     class="h-14 w-14 shrink-0 rounded-lg bg-white object-contain p-0.5">
                <div class="leading-tight">
                    <p class="font-display text-lg font-semibold">{{ $site['short_name'] }}</p>
                    <p class="text-[0.6rem] font-semibold uppercase tracking-[0.2em] text-white/45">
                        {{ __('site.nav.brand_sub') }}
                    </p>
                </div>
            </div>

            <p class="mt-5 max-w-xs text-sm leading-relaxed text-white/60">
                {{ __('site.footer.about') }}
            </p>

            <a href="{{ $site['whatsapp_url'] }}" class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-gold transition hover:text-white">
                <x-site.icon name="whatsapp" class="h-4 w-4" />
                {{ __('site.actions.whatsapp') }}
            </a>
        </div>

        <div>
            <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-gold">{{ __('site.footer.quick_links') }}</h2>
            <ul class="mt-5 space-y-3 text-sm text-white/70">
                @foreach ([
                    'home' => __('site.nav.home'),
                    'about' => __('site.nav.about'),
                    'services' => __('site.nav.services'),
                    'portfolio.index' => __('site.nav.portfolio'),
                    'contact' => __('site.nav.contact'),
                ] as $routeName => $label)
                    <li>
                        <a href="{{ SiteRoutes::url($routeName) }}" class="transition hover:text-gold">{{ $label }}</a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div>
            <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-gold">{{ __('site.footer.services_title') }}</h2>
            <ul class="mt-5 space-y-3 text-sm text-white/70">
                @foreach (array_slice($siteServices, 0, 6) as $service)
                    <li>
                        <a href="{{ SiteRoutes::url('services') }}#{{ $service['slug'] }}" class="transition hover:text-gold">
                            {{ $service['name'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div>
            <h2 class="text-xs font-semibold uppercase tracking-[0.2em] text-gold">{{ __('site.footer.contact_title') }}</h2>

            <address class="mt-5 space-y-1 text-sm not-italic leading-relaxed text-white/70">
                @foreach ($site['address'] as $line)
                    <span class="block">{{ $line }}</span>
                @endforeach
            </address>

            <ul class="mt-5 space-y-2 text-sm">
                @foreach ($site['phones'] as $phone)
                    <li>
                        <a href="{{ $phone['tel'] }}" class="flex items-center gap-2 text-white/70 transition hover:text-gold">
                            <x-site.icon name="phone" class="h-3.5 w-3.5 text-gold/70" />
                            <span>{{ $phone['display'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="relative border-t border-white/10">
        <div class="site-container flex flex-col gap-3 py-6 text-xs text-white/45 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ date('Y') }} {{ $site['name'] }}. {{ __('site.footer.rights') }}</p>

            <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                <span>{{ __('site.footer.sample_note') }}</span>
                <a href="{{ \App\Support\Site\SiteRoutes::url('credits') }}" class="transition hover:text-gold">{{ __('site.footer.credits') }}</a>
                <a href="{{ route('admin.login') }}" class="transition hover:text-gold">{{ __('site.footer.staff') }}</a>
            </div>
        </div>
    </div>
</footer>
