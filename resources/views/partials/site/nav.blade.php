@use('App\Support\Site\SiteRoutes')

<header data-site-header class="sticky top-0 z-50 border-b border-ink/10 bg-white/95 backdrop-blur transition-shadow">
    <div class="hidden bg-brand-dark text-white lg:block">
        <div class="site-container flex items-center justify-between py-2 text-[0.7rem] tracking-wide">
            <p class="flex items-center gap-2 text-white/70">
                <x-site.icon name="pin" class="h-3.5 w-3.5 text-gold" />
                {{ $site['address'][0] }}, {{ $site['address'][2] }}
            </p>

            <div class="flex items-center gap-5">
                <span class="text-white/50">{{ $site['tagline'] }}</span>
                <a href="{{ $site['phones'][0]['tel'] }}" class="flex items-center gap-1.5 font-semibold text-gold transition hover:text-white">
                    <x-site.icon name="phone" class="h-3.5 w-3.5" />
                    {{ $site['phones'][0]['display'] }}
                </a>
            </div>
        </div>
    </div>

    <div class="site-container flex items-center justify-between gap-6 py-4">
        <a href="{{ SiteRoutes::url('home') }}" class="flex items-center gap-3" aria-label="{{ $site['name'] }}">
            <span class="grid h-11 w-11 place-items-center rounded-lg bg-brand font-display text-xl font-semibold text-gold shadow-sm">
                {{ mb_substr($site['short_name'], 0, 1) }}
            </span>
            <span class="leading-tight">
                <span class="block font-display text-[1.05rem] font-semibold tracking-tight text-brand-dark">
                    {{ $site['short_name'] }}
                </span>
                <span class="block text-[0.6rem] font-semibold uppercase tracking-[0.2em] text-ink/45">
                    {{ __('site.nav.brand_sub') }}
                </span>
            </span>
        </a>

        <nav class="hidden items-center gap-7 lg:flex" aria-label="{{ __('site.nav.menu') }}">
            @foreach ([
                'home' => __('site.nav.home'),
                'about' => __('site.nav.about'),
                'services' => __('site.nav.services'),
                'portfolio.index' => __('site.nav.portfolio'),
                'contact' => __('site.nav.contact'),
            ] as $routeName => $label)
                <a href="{{ SiteRoutes::url($routeName) }}"
                   @class(['nav-link', 'nav-link-active' => SiteRoutes::isActive($routeName)])>
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        <div class="hidden items-center gap-4 lg:flex">
            <a href="{{ SiteRoutes::switchUrl(app()->getLocale() === 'mr' ? 'en' : 'mr') }}"
               hreflang="{{ app()->getLocale() === 'mr' ? 'en' : 'mr' }}"
               class="inline-flex items-center gap-1.5 rounded-full border border-ink/10 px-3 py-1.5 text-xs font-semibold text-ink/60 transition hover:border-brand/30 hover:text-brand">
                <x-site.icon name="globe" class="h-3.5 w-3.5" />
                {{ app()->getLocale() === 'mr' ? 'English' : 'मराठी' }}
            </a>

            <x-site.button :href="$site['whatsapp_url']" variant="primary" size="sm" icon="arrow-up-right">
                {{ __('site.actions.request_quote') }}
            </x-site.button>
        </div>

        <button type="button" data-site-menu-toggle aria-expanded="false" aria-controls="site-menu"
                class="grid h-10 w-10 place-items-center rounded-lg border border-ink/10 text-brand lg:hidden">
            <span class="sr-only">{{ __('site.nav.menu') }}</span>
            <x-site.icon name="menu" class="h-5 w-5" />
        </button>
    </div>

    <div id="site-menu" data-site-menu class="hidden border-t border-ink/10 bg-white lg:hidden">
        <nav class="site-container flex flex-col gap-1 py-4" aria-label="{{ __('site.nav.menu') }}">
            @foreach ([
                'home' => __('site.nav.home'),
                'about' => __('site.nav.about'),
                'services' => __('site.nav.services'),
                'portfolio.index' => __('site.nav.portfolio'),
                'contact' => __('site.nav.contact'),
            ] as $routeName => $label)
                <a href="{{ SiteRoutes::url($routeName) }}"
                   @class([
                       'rounded-lg px-3 py-2.5 text-sm font-medium',
                       'bg-sand text-brand' => SiteRoutes::isActive($routeName),
                       'text-ink/70' => ! SiteRoutes::isActive($routeName),
                   ])>
                    {{ $label }}
                </a>
            @endforeach

            <div class="mt-3 flex flex-col gap-2 border-t border-ink/10 pt-4">
                <a href="{{ $site['phones'][0]['tel'] }}" class="flex items-center gap-2 px-3 text-sm font-semibold text-brand">
                    <x-site.icon name="phone" class="h-4 w-4" />
                    {{ $site['phones'][0]['display'] }}
                </a>

                <div class="flex items-center gap-2 px-3 pt-1">
                    <a href="{{ SiteRoutes::switchUrl(app()->getLocale() === 'mr' ? 'en' : 'mr') }}"
                       class="inline-flex items-center gap-1.5 rounded-full border border-ink/10 px-3 py-1.5 text-xs font-semibold text-ink/60">
                        <x-site.icon name="globe" class="h-3.5 w-3.5" />
                        {{ app()->getLocale() === 'mr' ? 'English' : 'मराठी' }}
                    </a>
                </div>
            </div>
        </nav>
    </div>
</header>
