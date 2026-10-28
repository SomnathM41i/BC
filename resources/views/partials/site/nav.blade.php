@use('App\Support\Site\SiteRoutes')

<header data-site-header class="sticky top-0 z-50 border-b border-ink/10 bg-white/95 backdrop-blur transition-shadow">
    <div class="bg-brand-dark text-white">
        <div class="site-container flex items-center justify-start gap-4 py-2 text-[0.75rem] sm:justify-between sm:gap-6">
            <p class="flex min-w-0 items-center gap-2 text-white/75">
                <x-site.icon name="pin" class="h-3.5 w-3.5 shrink-0 text-gold" />
                <span class="truncate sm:hidden">{{ $site['address_compact'] }}</span>
                <span class="hidden truncate sm:inline">{{ $site['address_short'] }}</span>
            </p>

            <div class="flex shrink-0 items-center gap-5">
                <span class="hidden text-white/50 xl:inline">{{ $site['tagline'] }}</span>
                <a href="{{ $site['phones'][0]['tel'] }}" class="flex items-center gap-1.5 font-semibold text-gold transition hover:text-white">
                    <x-site.icon name="phone" class="h-3.5 w-3.5" />
                    {{ $site['phones'][0]['display'] }}
                </a>
            </div>
        </div>
    </div>

    <div class="site-container flex items-center justify-between gap-4 py-3 sm:gap-6">
        <a href="{{ SiteRoutes::url('home') }}" class="flex shrink-0 items-center">
            <img src="{{ $site['logo_header'] }}"
                 alt="{{ $site['name'] }} — Construction &amp; Developers"
                 width="800" height="193" decoding="async" fetchpriority="high"
                 class="h-11 w-auto max-w-[11.5rem] object-contain sm:h-12 sm:max-w-[13rem] lg:h-14 lg:max-w-none">
        </a>

        <nav class="hidden items-center gap-6 nav:flex" aria-label="{{ __('site.nav.main_navigation') }}">
            @foreach ([
                'home' => __('site.nav.home'),
                'about' => __('site.nav.about'),
                'services' => __('site.nav.services'),
                'portfolio.index' => __('site.nav.portfolio'),
                'contact' => __('site.nav.contact'),
            ] as $routeName => $label)
                <a href="{{ SiteRoutes::url($routeName) }}"
                   @if (SiteRoutes::isActive($routeName)) aria-current="page" @endif
                   @class(['nav-link', 'nav-link-active' => SiteRoutes::isActive($routeName)])>
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        <div class="hidden items-center gap-4 nav:flex">
            <div class="inline-flex items-center rounded-full border border-ink/10 bg-sand/60 p-1 text-xs font-semibold" aria-label="{{ __('site.nav.language') }}">
                <x-site.icon name="globe" class="h-3.5 w-3.5" />
                <a href="{{ SiteRoutes::switchUrl('mr') }}" hreflang="mr-IN"
                   @class(['rounded-full px-2 py-1 text-brand' => app()->getLocale() === 'mr', 'px-2 py-1 text-ink/55 transition hover:text-brand' => app()->getLocale() !== 'mr'])>
                    {{ __('site.nav.language_marathi') }}
                </a>
                <span class="text-ink/25" aria-hidden="true">|</span>
                <a href="{{ SiteRoutes::switchUrl('en') }}" hreflang="en-IN"
                   @class(['rounded-full px-2 py-1 text-brand' => app()->getLocale() === 'en', 'px-2 py-1 text-ink/55 transition hover:text-brand' => app()->getLocale() !== 'en'])>
                    {{ __('site.nav.language_english') }}
                </a>
            </div>

            <x-site.button :href="$site['whatsapp_url']" variant="primary" size="sm" icon="arrow-up-right">
                {{ __('site.actions.discuss_project') }}
            </x-site.button>
        </div>

        <button type="button" data-site-menu-toggle aria-expanded="false" aria-controls="site-menu"
                aria-label="{{ __('site.nav.menu') }}"
                data-close-label="{{ __('site.nav.close') }}"
                class="grid h-10 w-10 place-items-center rounded-lg border border-ink/10 text-brand nav:hidden">
            <x-site.icon name="menu" data-site-menu-icon="closed" class="h-5 w-5" />
            <x-site.icon name="close" data-site-menu-icon="open" class="hidden h-5 w-5" />
        </button>
    </div>
</header>

{{-- Mobile navigation: slides in from the left edge of the screen. --}}
<div data-site-menu-backdrop
     class="pointer-events-none fixed inset-0 z-[60] bg-ink/45 opacity-0 backdrop-blur-[2px] transition-opacity duration-300 nav:hidden"
     aria-hidden="true"></div>

<aside id="site-menu" data-site-menu aria-hidden="true" inert aria-label="{{ __('site.nav.main_navigation') }}"
       class="fixed inset-y-0 left-0 z-[70] flex w-[min(20rem,86vw)] -translate-x-full flex-col overflow-y-auto overscroll-contain border-r border-ink/10 bg-white shadow-2xl transition-transform duration-300 ease-out nav:hidden">

    <div class="flex items-center justify-between gap-3 border-b border-ink/10 px-4 py-3">
        <a href="{{ SiteRoutes::url('home') }}" class="flex items-center" aria-label="{{ $site['name'] }}">
            <img src="{{ $site['logo_header'] }}" alt="{{ $site['name'] }} — Construction &amp; Developers"
                 width="800" height="193" decoding="async"
                 class="h-9 w-auto max-w-[9.5rem] object-contain">
        </a>

        <button type="button" data-site-menu-close aria-label="{{ __('site.nav.close') }}"
                class="grid h-10 w-10 shrink-0 place-items-center rounded-lg border border-ink/10 text-brand transition hover:bg-sand">
            <x-site.icon name="close" class="h-5 w-5" />
        </button>
    </div>

    <nav class="flex flex-1 flex-col gap-1 px-3 py-4">
        @foreach ([
            'home' => __('site.nav.home'),
            'about' => __('site.nav.about'),
            'services' => __('site.nav.services'),
            'portfolio.index' => __('site.nav.portfolio'),
            'contact' => __('site.nav.contact'),
        ] as $routeName => $label)
                <a href="{{ SiteRoutes::url($routeName) }}"
                   @if (SiteRoutes::isActive($routeName)) aria-current="page" @endif
               @class([
                   'rounded-lg px-3 py-2.5 text-sm font-bold',
                   'bg-sand text-brand' => SiteRoutes::isActive($routeName),
                   'text-ink/75' => ! SiteRoutes::isActive($routeName),
               ])>
                {{ $label }}
            </a>
        @endforeach

        <div class="mt-3 flex flex-col gap-2 border-t border-ink/10 pt-4">
            <a href="{{ $site['phones'][0]['tel'] }}" class="flex items-center gap-2 px-3 text-sm font-semibold text-brand">
                <x-site.icon name="phone" class="h-4 w-4" />
                {{ $site['phones'][0]['display'] }}
            </a>

            <a href="{{ $site['whatsapp_url'] }}" target="_blank" rel="noopener"
               class="mx-3 inline-flex min-h-11 items-center justify-center gap-2 rounded-full bg-brand px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-dark">
                <x-site.icon name="whatsapp" class="h-4 w-4" />
                {{ __('site.actions.whatsapp') }}
            </a>

            <div class="flex items-center gap-2 px-3 pt-1">
                <div class="inline-flex items-center rounded-full border border-ink/10 bg-sand/60 p-1 text-xs font-semibold" aria-label="{{ __('site.nav.language') }}">
                    <x-site.icon name="globe" class="h-3.5 w-3.5" />
                    <a href="{{ SiteRoutes::switchUrl('mr') }}" hreflang="mr-IN"
                       @class(['rounded-full px-2 py-1 text-brand' => app()->getLocale() === 'mr', 'px-2 py-1 text-ink/55' => app()->getLocale() !== 'mr'])>
                        {{ __('site.nav.language_marathi') }}
                    </a>
                    <span class="text-ink/25" aria-hidden="true">|</span>
                    <a href="{{ SiteRoutes::switchUrl('en') }}" hreflang="en-IN"
                       @class(['rounded-full px-2 py-1 text-brand' => app()->getLocale() === 'en', 'px-2 py-1 text-ink/55' => app()->getLocale() !== 'en'])>
                        {{ __('site.nav.language_english') }}
                    </a>
                </div>
            </div>
        </div>
    </nav>
</aside>
