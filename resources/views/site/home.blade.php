@extends('layouts.site')
@use('App\Support\Site\SiteRoutes')

@section('title', __('site.meta.home.title'))
@section('description', __('site.meta.home.description'))

@php
    $whyIcons = ['home', 'calculator', 'shield', 'handshake', 'compass', 'clipboard'];
    $testimonials = config('site.testimonials', []);
@endphp

@push('head')
    <link rel="preload" as="image" href="{{ asset('images/site/portfolio/modern-duplex-residence/cover.jpg') }}">
@endpush

@section('content')
    {{-- Hero --}}
    <section class="relative isolate overflow-hidden bg-brand-dark">
        <img src="{{ asset('images/site/portfolio/modern-duplex-residence/cover.jpg') }}"
             alt="{{ __('site.home.hero_image_alt') }}"
             class="absolute inset-0 h-full w-full object-cover object-[68%_center] sm:object-center"
             fetchpriority="high" decoding="async">

        <div class="absolute inset-0 bg-gradient-to-r from-brand-dark via-brand-dark/90 to-brand-dark/25" aria-hidden="true"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-brand-dark/80 via-transparent to-brand-dark/10" aria-hidden="true"></div>

        <div class="site-container relative flex min-h-[540px] flex-col justify-center py-16 sm:min-h-[600px] lg:min-h-[640px] lg:py-24">
            <div class="max-w-2xl">
                <span class="eyebrow eyebrow-light">
                    <span class="h-px w-7 bg-current"></span>
                    {{ __('site.home.hero_eyebrow') }}
                </span>

                <h1 class="mt-5 font-display text-4xl font-semibold leading-[1.08] tracking-tight text-white text-balance sm:text-5xl lg:text-[3.6rem]">
                    {{ __('site.home.hero_title') }}
                </h1>

                <p class="mt-5 max-w-xl text-base leading-relaxed text-white/85 sm:text-lg">
                    {{ __('site.home.hero_lead') }}
                </p>

                <p class="mt-3 max-w-xl text-sm leading-relaxed text-white/65 sm:text-base">
                    {{ __('site.home.hero_body') }}
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    <x-site.button :href="SiteRoutes::url('contact')" variant="gold" icon="arrow-right">
                        {{ __('site.actions.discuss_project') }}
                    </x-site.button>
                    <x-site.button :href="SiteRoutes::url('portfolio.index')" variant="outline-light">
                        {{ __('site.actions.explore_projects') }}
                    </x-site.button>
                </div>

                <ul class="mt-9 flex flex-wrap items-center gap-x-5 gap-y-2 border-t border-white/15 pt-5 text-[0.7rem] font-semibold uppercase tracking-[0.14em] text-white/60">
                    @foreach (__('site.home.hero_services') as $service)
                        <li class="flex items-center gap-2">
                            <span class="h-1 w-1 rounded-full bg-gold" aria-hidden="true"></span>
                            {{ $service }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <p class="absolute bottom-4 right-5 text-[0.65rem] text-white/40">{{ __('site.home.hero_image_note') }}</p>
    </section>

    {{-- Trust strip --}}
    <section class="border-y border-gold/30 bg-gold/10">
        <div class="site-container no-scrollbar flex snap-x snap-mandatory gap-3 overflow-x-auto py-5 sm:grid sm:snap-none sm:gap-4 sm:overflow-visible sm:py-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (__('site.home.trust') as $item)
                <div class="flex shrink-0 snap-start items-center gap-3 rounded-xl border border-gold/40 bg-white/80 px-4 py-3 shadow-sm sm:shrink sm:rounded-none sm:border-0 sm:bg-transparent sm:px-0 sm:py-0 sm:shadow-none">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-brand/10 text-brand sm:bg-sand">
                        <x-site.icon :name="$item['icon']" class="h-5 w-5" />
                    </span>
                    <p class="whitespace-nowrap text-sm font-semibold text-ink/75 sm:whitespace-normal">{{ $item['label'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- About --}}
    <section class="bg-white py-20 lg:py-24">
        <div class="site-container">
            <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
                <div class="reveal relative">
                    <div class="absolute -left-4 -top-4 h-24 w-24 rounded-tl-2xl border-l-2 border-t-2 border-gold" aria-hidden="true"></div>
                    <div class="overflow-hidden rounded-2xl">
                        <img src="{{ asset('images/site/portfolio/modern-duplex-residence/01.jpg') }}"
                             alt="{{ __('site.home.about_image_alt') }}"
                             class="aspect-[4/5] w-full object-cover" loading="lazy" decoding="async">
                    </div>
                    <div class="absolute -bottom-5 left-5 rounded-xl bg-brand px-5 py-4 text-white shadow-lg sm:left-8">
                        <p class="font-display text-lg font-semibold">{{ $site['short_name'] }}</p>
                        <p class="text-[0.65rem] uppercase tracking-[0.16em] text-white/60">{{ $site['address_compact'] }}</p>
                    </div>
                </div>

                <div class="reveal">
                    <x-site.section-heading
                        :eyebrow="__('site.home.about_eyebrow')"
                        :title="__('site.home.about_title')"
                        :lead="__('site.home.about_lead')" />

                    <p class="mt-5 text-sm leading-relaxed text-ink/65 sm:text-base">
                        {{ __('site.home.about_body') }}
                    </p>

                    <ul class="mt-7 grid gap-3 sm:grid-cols-2">
                        @foreach (__('site.home.about_points') as $point)
                            <li class="flex items-start gap-2.5 text-sm text-ink/70">
                                <x-site.icon name="check-circle" class="mt-0.5 h-4 w-4 shrink-0 text-brand-red" />
                                <span>{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <div class="mt-8">
                        <x-site.button :href="SiteRoutes::url('about')" variant="outline" icon="arrow-right">
                            {{ __('site.actions.read_about') }}
                        </x-site.button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Services --}}
    <section class="bg-[#f1ece5] py-20 lg:py-24">
        <div class="site-container">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <x-site.section-heading
                    :eyebrow="__('site.home.services_eyebrow')"
                    :title="__('site.home.services_title')"
                    :lead="__('site.home.services_lead')" />

                <x-site.button :href="SiteRoutes::url('services')" variant="outline" size="sm" icon="arrow-right" class="shrink-0">
                    {{ __('site.actions.view_all_services') }}
                </x-site.button>
            </div>

            <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($services as $index => $service)
                    <div class="reveal" style="transition-delay: {{ $index * 60 }}ms">
                        <x-site.service-card :service="$service" :index="$index + 1" />
                    </div>
                @endforeach
            </div>

            <p class="mt-8 max-w-3xl text-xs leading-relaxed text-ink/45">
                {{ __('site.home.services_note') }}
            </p>
        </div>
    </section>

    {{-- Projects --}}
    <section class="bg-[#f0f4f3] py-20 lg:py-24">
        <div class="site-container">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <x-site.section-heading
                    :eyebrow="__('site.home.projects_eyebrow')"
                    :title="__('site.home.projects_title')"
                    :lead="__('site.home.projects_lead')" />

                <x-site.button :href="SiteRoutes::url('portfolio.index')" variant="outline" size="sm" icon="arrow-right" class="shrink-0">
                    {{ __('site.actions.view_all_projects') }}
                </x-site.button>
            </div>

            @if ($projects->isNotEmpty())
                <div class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($projects as $project)
                        <div class="reveal">
                            <x-site.project-card :project="$project" />
                        </div>
                    @endforeach
                </div>

                <p class="mt-8 text-xs leading-relaxed text-ink/45">{{ __('site.home.projects_note') }}</p>
            @else
                <div class="mt-12 rounded-2xl border border-dashed border-ink/15 bg-white/60 p-10 text-center">
                    <p class="font-display text-xl font-semibold text-brand-dark">{{ __('site.portfolio.empty_title') }}</p>
                    <p class="mx-auto mt-2 max-w-md text-sm text-ink/55">{{ __('site.portfolio.empty_text') }}</p>
                </div>
            @endif
        </div>
    </section>

    {{-- Why us --}}
    <section class="border-y border-brand/10 py-20 lg:py-24">
        <div class="site-container">
            <x-site.section-heading
                :eyebrow="__('site.home.why_eyebrow')"
                :title="__('site.home.why_title')"
                :lead="__('site.home.why_lead')"
                align="center" />

            <div class="mx-auto mt-12 grid max-w-5xl gap-x-10 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
                @foreach (__('site.home.why_items') as $index => $item)
                    <div class="reveal border-t border-ink/10 pt-6">
                        <span class="grid h-10 w-10 place-items-center rounded-lg bg-sand text-brand">
                            <x-site.icon :name="$whyIcons[$index] ?? 'check'" class="h-5 w-5" />
                        </span>
                        <h3 class="mt-4 font-display text-lg font-semibold text-brand-dark">{{ $item['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-ink/60">{{ $item['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Process --}}
    <section class="bg-[#edf3f5] py-20 lg:py-24">
        <div class="site-container">
            <x-site.section-heading
                :eyebrow="__('site.home.process_eyebrow')"
                :title="__('site.home.process_title')"
                :lead="__('site.home.process_lead')"
                align="center" />

            <ol class="mt-14 grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                @foreach (__('site.home.process_steps') as $index => $step)
                    <li class="reveal relative" style="transition-delay: {{ $index * 80 }}ms">
                        <div class="flex items-center gap-3">
                            <span class="font-display text-3xl font-semibold text-gold">
                                {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <span class="h-px flex-1 bg-ink/10"></span>
                        </div>
                        <h3 class="mt-4 font-display text-lg font-semibold text-brand-dark">{{ $step['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-ink/60">{{ $step['text'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Testimonials: only when real, approved reviews exist in config/site.php --}}
    @if (! empty($testimonials))
        <section class="site-container py-20 lg:py-24">
            <x-site.section-heading
                :eyebrow="__('site.home.testimonials_eyebrow')"
                :title="__('site.home.testimonials_title')"
                :lead="__('site.home.testimonials_lead')"
                align="center" />

            <div class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($testimonials as $review)
                    <figure class="site-card flex h-full flex-col p-6">
                        <x-site.icon name="star" class="h-5 w-5 text-gold" />
                        <blockquote class="mt-4 flex-1 text-sm leading-relaxed text-ink/70">
                            “{{ $review['quote'] }}”
                        </blockquote>
                        <figcaption class="mt-5 border-t border-ink/10 pt-4">
                            <p class="text-sm font-semibold text-brand-dark">{{ $review['name'] }}</p>
                            @isset($review['context'])
                                <p class="mt-0.5 text-xs text-ink/50">{{ $review['context'] }}</p>
                            @endisset
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </section>
    @endif

    {{-- CTA --}}
    <section class="relative overflow-hidden">
        <img src="{{ asset('images/site/portfolio/interior-design-collection/cover.jpg') }}"
             alt="{{ __('site.home.cta_image_alt') }}"
             class="absolute inset-0 h-full w-full object-cover" loading="lazy" decoding="async">
        <div class="absolute inset-0 bg-brand-dark/90"></div>

        <div class="site-container relative py-16 text-center sm:py-20">
            <h2 class="mx-auto max-w-2xl font-display text-3xl font-semibold leading-tight text-white text-balance sm:text-4xl">
                {{ __('site.home.cta_title') }}
            </h2>
            <p class="mx-auto mt-4 max-w-xl text-base text-white/70">{{ __('site.home.cta_text') }}</p>

            <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row sm:flex-wrap">
                <x-site.button :href="SiteRoutes::url('contact')" variant="gold" icon="arrow-right">
                    {{ __('site.actions.discuss_project') }}
                </x-site.button>
                <x-site.button :href="$site['whatsapp_url']" variant="outline-light" icon="arrow-up-right">
                    {{ __('site.actions.whatsapp') }}
                </x-site.button>
            </div>
        </div>
    </section>

    {{-- Contact preview --}}
    <section class="bg-[#f8efe8] py-20 lg:py-24">
        <div class="site-container">
            <div class="grid gap-10 rounded-2xl border border-ink/10 bg-white p-8 sm:p-10 lg:grid-cols-[1.1fr_1fr] lg:p-12">
                <div>
                    <x-site.section-heading
                        :eyebrow="__('site.home.contact_eyebrow')"
                        :title="__('site.home.contact_title')"
                        :lead="__('site.home.contact_lead')" />

                    <div class="mt-8 flex flex-wrap gap-3">
                        <x-site.button :href="SiteRoutes::url('contact')" icon="arrow-right">
                            {{ __('site.actions.contact_us') }}
                        </x-site.button>
                        <x-site.button :href="$site['phones'][0]['tel']" variant="outline">
                            {{ __('site.actions.call_now') }}
                        </x-site.button>
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-1">
                    <div class="rounded-xl bg-sand p-5">
                        <p class="text-[0.65rem] font-semibold uppercase tracking-[0.18em] text-ink/45">{{ __('site.contact.phone_label') }}</p>
                        <ul class="mt-3 space-y-2">
                            @foreach ($site['phones'] as $phone)
                                <li>
                                    <a href="{{ $phone['tel'] }}" class="flex items-center justify-between gap-3 text-sm text-ink/75 transition hover:text-brand">
                                        <span>{{ $phone['name'] }}</span>
                                        <span class="font-semibold text-brand">{{ $phone['display'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="rounded-xl bg-sand p-5">
                        <p class="text-[0.65rem] font-semibold uppercase tracking-[0.18em] text-ink/45">{{ __('site.contact.address_label') }}</p>
                        <address class="mt-3 space-y-1 text-sm not-italic leading-relaxed text-ink/75">
                            @foreach ($site['address'] as $line)
                                <span class="block">{{ $line }}</span>
                            @endforeach
                        </address>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
