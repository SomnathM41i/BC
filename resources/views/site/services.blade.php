@extends('layouts.site')
@use('App\Support\Site\SiteRoutes')

@section('title', __('site.meta.services.title'))
@section('description', __('site.meta.services.description'))

@section('content')
    <x-site.page-hero
        :eyebrow="__('site.services.banner_eyebrow')"
        :title="__('site.services.banner_title')"
        :lead="__('site.services.banner_lead')" />

    <section class="border-b border-ink/10 bg-white">
        <div class="site-container py-10">
            <div class="grid gap-8 lg:grid-cols-[0.85fr_1.15fr] lg:items-center">
                <div>
                    <h2 class="font-display text-2xl font-semibold text-brand-dark">{{ __('site.services.intro_title') }}</h2>
                    <p class="mt-3 text-sm leading-relaxed text-ink/65">{{ __('site.services.intro_body') }}</p>
                </div>

                <nav class="flex flex-wrap gap-2" aria-label="{{ __('site.nav.services') }}">
                    @foreach ($services as $service)
                        <a href="#{{ $service['slug'] }}" class="chip">{{ $service['name'] }}</a>
                    @endforeach
                </nav>
            </div>
        </div>
    </section>

    {{-- How an engagement starts --}}
    <section class="bg-[#f1ece5]">
        <div class="site-container py-12">
            <h2 class="font-display text-xl font-semibold text-brand-dark sm:text-2xl">{{ __('site.services.engagement_title') }}</h2>

            <ol class="mt-7 grid gap-6 sm:grid-cols-3">
                @foreach (__('site.services.engagement_steps') as $index => $step)
                    <li class="flex gap-3">
                        <span class="mt-0.5 grid h-8 w-8 shrink-0 place-items-center rounded-full bg-brand text-xs font-semibold text-white">
                            {{ $index + 1 }}
                        </span>
                        <div>
                            <h3 class="text-sm font-semibold text-brand-dark">{{ $step['title'] }}</h3>
                            <p class="mt-1 text-sm leading-relaxed text-ink/60">{{ $step['text'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section class="site-container py-16 lg:py-20">
        <div class="grid gap-6 md:grid-cols-2">
            @foreach ($services as $index => $service)
                <article id="{{ $service['slug'] }}" class="reveal site-card flex flex-col scroll-mt-28 p-7">
                    <div class="flex items-start justify-between gap-4">
                        <span class="grid h-12 w-12 place-items-center rounded-xl bg-sand text-brand">
                            <x-site.icon :name="$service['icon']" class="h-6 w-6" />
                        </span>
                        <span class="font-display text-sm font-semibold text-ink/20">
                            {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                        </span>
                    </div>

                    <h3 class="mt-5 font-display text-xl font-semibold text-brand-dark">{{ $service['name'] }}</h3>
                    <p class="mt-2 text-sm font-medium text-brand-red">{{ $service['short'] }}</p>
                    <p class="mt-3 text-sm leading-relaxed text-ink/65">{{ $service['description'] }}</p>

                    @if (! empty($service['benefits']))
                        <ul class="mt-5 flex-1 space-y-2 border-t border-ink/5 pt-4">
                            @foreach ($service['benefits'] as $benefit)
                                <li class="flex items-start gap-2 text-sm text-ink/70">
                                    <x-site.icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-brand-red" />
                                    <span>{{ $benefit }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <a href="{{ SiteRoutes::url('contact', ['service' => $service['slug']]) }}#enquiry-form"
                       class="dev-label mt-6 inline-flex items-center gap-2 text-[0.7rem] font-semibold uppercase tracking-[0.16em] text-brand transition hover:text-brand-red">
                        {{ __('site.actions.enquire_now') }}
                        <x-site.icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </article>
            @endforeach
        </div>

        <p class="mt-10 max-w-3xl rounded-xl bg-white/70 p-5 text-xs leading-relaxed text-ink/50">
            {{ __('site.home.services_note') }}
        </p>
    </section>

    <section class="bg-brand-dark">
        <div class="site-container flex flex-col items-start gap-6 py-14 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-display text-2xl font-semibold text-white sm:text-3xl">{{ __('site.services.cta_title') }}</h2>
                <p class="mt-2 max-w-xl text-sm text-white/65">{{ __('site.services.cta_text') }}</p>
            </div>

            <div class="flex shrink-0 flex-wrap gap-3">
                <x-site.button :href="SiteRoutes::url('contact')" variant="gold" icon="arrow-right">
                    {{ __('site.actions.discuss_project') }}
                </x-site.button>
                <x-site.button :href="$site['phones'][0]['tel']" variant="outline-light">
                    {{ __('site.actions.call_now') }}
                </x-site.button>
            </div>
        </div>
    </section>
@endsection
