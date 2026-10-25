@extends('layouts.site')
@use('App\Support\Site\SiteRoutes')

@section('title', __('site.meta.home.title'))
@section('description', __('site.meta.home.description'))

@php
    $whyIcons = ['shield', 'compass', 'handshake', 'home', 'layers', 'check-circle'];
@endphp

@section('content')
    {{-- Hero --}}
    <section class="relative overflow-hidden bg-brand-dark">
        <div class="absolute inset-0 bg-grid opacity-40" aria-hidden="true"></div>
        <div class="absolute -left-24 top-10 h-72 w-72 rounded-full bg-brand-red/20 blur-3xl" aria-hidden="true"></div>

        <div class="relative grid lg:grid-cols-[1.02fr_1fr]">
            <div class="flex items-center">
                <div class="w-full max-w-2xl px-5 py-16 sm:px-8 sm:py-20 lg:py-24 lg:pl-12 xl:pl-16">
                    <span class="eyebrow eyebrow-light">
                        <span class="h-px w-8 bg-current"></span>
                        {{ __('site.home.hero_eyebrow') }}
                    </span>

                    <h1 class="site-hero-title mt-5 font-display text-[2.4rem] font-semibold leading-[1.08] tracking-tight text-white text-balance sm:text-5xl lg:text-[3.5rem]">
                        {{ __('site.home.hero_title') }}
                    </h1>

                    <p class="mt-6 max-w-xl text-base leading-relaxed text-white/70 sm:text-lg">
                        {{ __('site.home.hero_lead') }}
                    </p>

                    <div class="mt-8 flex flex-wrap gap-3">
                        <x-site.button :href="SiteRoutes::url('portfolio.index')" variant="gold" icon="arrow-right">
                            {{ __('site.actions.explore_projects') }}
                        </x-site.button>
                        <x-site.button :href="SiteRoutes::url('contact')" variant="outline-light">
                            {{ __('site.actions.contact_us') }}
                        </x-site.button>
                    </div>

                    <ul class="mt-10 flex flex-wrap gap-x-6 gap-y-3 border-t border-white/10 pt-6">
                        @foreach (__('site.home.hero_support') as $support)
                            <li class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.14em] text-white/60">
                                <x-site.icon name="check" class="h-4 w-4 text-gold" />
                                {{ $support }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="relative min-h-[320px] sm:min-h-[420px] lg:min-h-full">
                <img src="{{ asset('images/site/photos/hero.jpg') }}" alt="{{ __('site.home.hero_image_alt') }}"
                     class="absolute inset-0 h-full w-full object-cover" fetchpriority="high">
                <div class="absolute inset-0 bg-gradient-to-r from-brand-dark/70 via-brand-dark/10 to-transparent lg:from-brand-dark/50"></div>

                <div class="absolute bottom-6 left-6 right-6 rounded-xl border border-white/15 bg-white/10 p-4 backdrop-blur-md sm:right-auto sm:max-w-xs">
                    <p class="text-[0.65rem] font-semibold uppercase tracking-[0.18em] text-gold">{{ __('site.nav.brand_sub') }}</p>
                    <p class="mt-1 text-sm leading-relaxed text-white/80">{{ $site['tagline'] }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Trust strip --}}
    <section class="border-b border-ink/10 bg-white">
        <div class="site-container grid gap-4 py-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (__('site.home.trust') as $item)
                <div class="flex items-center gap-3">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-sand text-brand">
                        <x-site.icon :name="$item['icon']" class="h-5 w-5" />
                    </span>
                    <p class="text-sm font-semibold text-ink/75">{{ $item['label'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- About --}}
    <section class="site-container py-20 lg:py-24">
        <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
            <div class="reveal relative">
                <div class="absolute -left-4 -top-4 h-24 w-24 rounded-tl-2xl border-l-2 border-t-2 border-gold" aria-hidden="true"></div>
                <div class="overflow-hidden rounded-2xl">
                    <img src="{{ asset('images/site/photos/about.jpg') }}" alt="{{ __('site.home.about_image_alt') }}"
                         class="aspect-[4/3] w-full object-cover" loading="lazy">
                </div>
                <div class="absolute -bottom-5 right-5 rounded-xl bg-brand px-5 py-4 text-white shadow-lg sm:right-8">
                    <p class="font-display text-lg font-semibold">{{ $site['short_name'] }}</p>
                    <p class="text-[0.65rem] uppercase tracking-[0.16em] text-white/60">{{ __('site.home.hero_eyebrow') }}</p>
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
    </section>

    {{-- Services --}}
    <section class="bg-white py-20 lg:py-24">
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
    <section class="site-container py-20 lg:py-24">
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
        @else
            <div class="mt-12 rounded-2xl border border-dashed border-ink/15 bg-white/60 p-10 text-center">
                <p class="font-display text-xl font-semibold text-brand-dark">{{ __('site.portfolio.empty_title') }}</p>
                <p class="mx-auto mt-2 max-w-md text-sm text-ink/55">{{ __('site.portfolio.empty_text') }}</p>
            </div>
        @endif
    </section>

    {{-- Why us --}}
    <section class="py-20 lg:py-24">
        <div class="site-container">
            <x-site.section-heading
                :eyebrow="__('site.home.why_eyebrow')"
                :title="__('site.home.why_title')"
                :lead="__('site.home.why_lead')"
                align="center" />

            <div class="mt-12 grid gap-x-8 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
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
    <section class="bg-white py-20 lg:py-24">
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

    {{-- CTA --}}
    <section class="relative overflow-hidden">
        <img src="{{ asset('images/site/photos/process.jpg') }}" alt="{{ __('site.home.cta_image_alt') }}"
             class="absolute inset-0 h-full w-full object-cover" loading="lazy">
        <div class="absolute inset-0 bg-brand-dark/85"></div>

        <div class="site-container relative py-16 text-center sm:py-20">
            <h2 class="mx-auto max-w-2xl font-display text-3xl font-semibold leading-tight text-white text-balance sm:text-4xl">
                {{ __('site.home.cta_title') }}
            </h2>
            <p class="mx-auto mt-4 max-w-xl text-base text-white/70">{{ __('site.home.cta_text') }}</p>

            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <x-site.button :href="SiteRoutes::url('contact')" variant="gold" icon="arrow-right">
                    {{ __('site.actions.send_enquiry') }}
                </x-site.button>
                <x-site.button :href="$site['phones'][0]['tel']" variant="outline-light">
                    {{ __('site.actions.call_now') }}
                </x-site.button>
            </div>
        </div>
    </section>

    {{-- Contact preview --}}
    <section class="site-container py-20 lg:py-24">
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
                    <x-site.button :href="$site['whatsapp_url']" variant="outline">
                        {{ __('site.actions.whatsapp') }}
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
    </section>
@endsection
