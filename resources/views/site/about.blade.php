@extends('layouts.site')
@use('App\Support\Site\SiteRoutes')

@section('title', __('site.meta.about.title'))
@section('description', __('site.meta.about.description'))

@php
    $principleIcons = ['shield', 'handshake', 'layers', 'compass', 'star'];
@endphp

@section('content')
    <x-site.page-hero
        :eyebrow="__('site.about.banner_eyebrow')"
        :title="__('site.about.banner_title')"
        :lead="__('site.about.banner_lead')" />

    {{-- Introduction --}}
    <section class="site-container py-20 lg:py-24">
        <div class="grid gap-12 lg:grid-cols-[0.9fr_1.1fr] lg:gap-16">
            <div class="reveal">
                <x-site.section-heading
                    :eyebrow="__('site.about.banner_eyebrow')"
                    :title="__('site.about.intro_title')" />

                <div class="mt-6 space-y-4">
                    @foreach (__('site.about.intro_body') as $paragraph)
                        <p class="text-sm leading-relaxed text-ink/70 sm:text-base">{{ $paragraph }}</p>
                    @endforeach
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    <x-site.button :href="SiteRoutes::url('services')" icon="arrow-right">
                        {{ __('site.actions.view_all_services') }}
                    </x-site.button>
                    <x-site.button :href="SiteRoutes::url('contact')" variant="outline">
                        {{ __('site.actions.contact_us') }}
                    </x-site.button>
                </div>
            </div>

            <div class="reveal">
                <div class="overflow-hidden rounded-2xl">
                    <img src="{{ asset('images/site/portfolio/terrace-residence/cover.jpg') }}"
                         alt="{{ __('site.home.about_image_alt') }}"
                         class="aspect-[4/3] w-full object-cover" loading="lazy" decoding="async">
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-ink/10 bg-white p-5">
                        <p class="text-[0.65rem] font-semibold uppercase tracking-[0.18em] text-brand-red">{{ __('site.contact.phone_label') }}</p>
                        <ul class="mt-2 space-y-1.5 text-sm text-ink/70">
                            @foreach ($site['phones'] as $phone)
                                <li>
                                    <a href="{{ $phone['tel'] }}" class="transition hover:text-brand">{{ $phone['name'] }} · {{ $phone['display'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="rounded-xl border border-ink/10 bg-white p-5">
                        <p class="text-[0.65rem] font-semibold uppercase tracking-[0.18em] text-brand-red">{{ __('site.contact.address_label') }}</p>
                        <address class="mt-2 space-y-1 text-sm not-italic leading-relaxed text-ink/70">
                            @foreach ($site['address'] as $line)
                                <span class="block">{{ $line }}</span>
                            @endforeach
                        </address>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- About the company --}}
    <section class="bg-brand-dark py-20 text-white lg:py-24">
        <div class="site-container grid gap-10 lg:grid-cols-[0.8fr_1.2fr] lg:gap-16">
            <div class="reveal">
                <span class="eyebrow eyebrow-light">
                    <span class="h-px w-7 bg-current"></span>
                    {{ __('site.about.banner_eyebrow') }}
                </span>
                <h2 class="mt-4 font-display text-3xl font-semibold leading-tight text-balance sm:text-4xl">
                    {{ __('site.about.company_title') }}
                </h2>
            </div>

            <div class="reveal grid gap-4 sm:grid-cols-2">
                @foreach (__('site.about.company_body') as $paragraph)
                    <p class="text-sm leading-relaxed text-white/70 sm:text-base">{{ $paragraph }}</p>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Approach --}}
    <section class="bg-white py-20 lg:py-24">
        <div class="site-container grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
            <div class="reveal order-2 lg:order-1">
                <x-site.section-heading
                    :eyebrow="__('site.home.why_eyebrow')"
                    :title="__('site.about.approach_title')" />

                <div class="mt-6 space-y-4">
                    @foreach (__('site.about.approach_body') as $paragraph)
                        <p class="text-sm leading-relaxed text-ink/70 sm:text-base">{{ $paragraph }}</p>
                    @endforeach
                </div>

                <ul class="mt-7 space-y-3">
                    @foreach (__('site.about.approach_points') as $point)
                        <li class="flex items-start gap-2.5 text-sm text-ink/70">
                            <x-site.icon name="check-circle" class="mt-0.5 h-4 w-4 shrink-0 text-brand-red" />
                            <span>{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="reveal order-1 lg:order-2">
                <div class="overflow-hidden rounded-2xl">
                    <img src="{{ asset('images/site/portfolio/vijaylakshmi-residence/cover.jpg') }}"
                         alt="{{ __('site.home.about_image_alt') }}"
                         class="aspect-[4/3] w-full object-cover" loading="lazy" decoding="async">
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
                    :title="__('site.about.services_title')"
                    :lead="__('site.about.services_lead')" />

                <x-site.button :href="SiteRoutes::url('services')" variant="outline" size="sm" icon="arrow-right" class="shrink-0">
                    {{ __('site.actions.view_all_services') }}
                </x-site.button>
            </div>

            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($services as $service)
                    <a href="{{ SiteRoutes::url('services') }}#{{ $service['slug'] }}"
                       class="group flex items-center gap-4 rounded-xl border border-ink/10 bg-white p-5 transition hover:-translate-y-0.5 hover:border-brand/25 hover:shadow-sm">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-sand text-brand transition group-hover:bg-brand group-hover:text-gold">
                            <x-site.icon :name="$service['icon']" class="h-5 w-5" />
                        </span>
                        <span class="text-sm font-semibold text-brand-dark">{{ $service['name'] }}</span>
                    </a>
                @endforeach
            </div>

            <p class="mt-8 text-xs leading-relaxed text-ink/45">{{ __('site.about.services_note') }}</p>
        </div>
    </section>

    {{-- Working principles --}}
    <section class="site-container py-20 lg:py-24">
        <x-site.section-heading
            :eyebrow="__('site.home.why_eyebrow')"
            :title="__('site.about.principles_title')"
            :lead="__('site.about.principles_lead')"
            align="center" />

        <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-5">
            @foreach (__('site.about.principles') as $index => $value)
                <div class="reveal site-card p-6" style="transition-delay: {{ $index * 60 }}ms">
                    <span class="grid h-10 w-10 place-items-center rounded-lg bg-sand text-brand">
                        <x-site.icon :name="$principleIcons[$index] ?? 'check'" class="h-5 w-5" />
                    </span>
                    <h3 class="mt-4 font-display text-lg font-semibold text-brand-dark">{{ $value['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-ink/60">{{ $value['text'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Quality and communication --}}
    <section class="bg-white py-20 lg:py-24">
        <div class="site-container grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
            <div class="reveal">
                <x-site.section-heading :title="__('site.about.quality_title')" />

                <div class="mt-6 space-y-4">
                    @foreach (__('site.about.quality_body') as $paragraph)
                        <p class="text-sm leading-relaxed text-ink/70 sm:text-base">{{ $paragraph }}</p>
                    @endforeach
                </div>

                <ul class="mt-7 space-y-3">
                    @foreach (__('site.home.about_points') as $point)
                        <li class="flex items-start gap-2.5 text-sm text-ink/70">
                            <x-site.icon name="check-circle" class="mt-0.5 h-4 w-4 shrink-0 text-brand-red" />
                            <span>{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="reveal">
                <div class="overflow-hidden rounded-2xl">
                    <img src="{{ asset('images/site/portfolio/terrace-residence/01.jpg') }}"
                         alt="{{ __('site.home.about_image_alt') }}"
                         class="aspect-[4/3] w-full object-cover" loading="lazy" decoding="async">
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="bg-brand-dark">
        <div class="site-container flex flex-col items-start gap-6 py-14 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-display text-2xl font-semibold text-white sm:text-3xl">{{ __('site.about.cta_title') }}</h2>
                <p class="mt-2 max-w-xl text-sm text-white/65">{{ __('site.about.cta_text') }}</p>
            </div>

            <div class="flex shrink-0 flex-wrap gap-3">
                <x-site.button :href="SiteRoutes::url('contact')" variant="gold" icon="arrow-right">
                    {{ __('site.actions.discuss_project') }}
                </x-site.button>
            </div>
        </div>
    </section>
@endsection
