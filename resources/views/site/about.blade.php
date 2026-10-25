@extends('layouts.site')
@use('App\Support\Site\SiteRoutes')

@section('title', __('site.meta.about.title'))
@section('description', __('site.meta.about.description'))

@php
    $valueIcons = ['shield', 'handshake', 'layers', 'star', 'spark'];
@endphp

@section('content')
    <x-site.page-hero
        :eyebrow="__('site.about.banner_eyebrow')"
        :title="__('site.about.banner_title')"
        :lead="__('site.about.banner_lead')" />

    {{-- Intro --}}
    <section class="site-container py-20 lg:py-24">
        <div class="grid gap-12 lg:grid-cols-[0.9fr_1.1fr] lg:gap-16">
            <div class="reveal">
                <x-site.section-heading :title="__('site.about.intro_title')" />

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
                    <img src="{{ asset('images/site/photos/about.jpg') }}" alt="{{ __('site.home.about_image_alt') }}"
                         class="aspect-[4/3] w-full object-cover" loading="lazy">
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-ink/10 bg-white p-5">
                        <p class="text-[0.65rem] font-semibold uppercase tracking-[0.18em] text-brand-red">{{ __('site.contact.phone_label') }}</p>
                        <ul class="mt-2 space-y-1.5 text-sm text-ink/70">
                            @foreach ($site['phones'] as $phone)
                                <li>{{ $phone['name'] }}</li>
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

    {{-- Philosophy --}}
    <section class="bg-white py-20 lg:py-24">
        <div class="site-container grid gap-12 lg:grid-cols-2 lg:gap-16">
            <div class="reveal">
                <x-site.section-heading
                    :eyebrow="__('site.home.why_eyebrow')"
                    :title="__('site.about.philosophy_title')" />
            </div>

            <div class="reveal space-y-4">
                @foreach (__('site.about.philosophy_body') as $paragraph)
                    <p class="text-sm leading-relaxed text-ink/70 sm:text-base">{{ $paragraph }}</p>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Mission & vision --}}
    <section class="site-container py-20 lg:py-24">
        <div class="grid gap-6 md:grid-cols-2">
            <div class="reveal relative overflow-hidden rounded-2xl bg-brand-dark p-8 text-white sm:p-10">
                <div class="absolute inset-0 bg-grid opacity-30" aria-hidden="true"></div>
                <div class="relative">
                    <span class="grid h-11 w-11 place-items-center rounded-lg bg-white/10 text-gold">
                        <x-site.icon name="compass" class="h-5 w-5" />
                    </span>
                    <h2 class="mt-5 font-display text-2xl font-semibold">{{ __('site.about.mission_title') }}</h2>
                    <p class="mt-3 text-sm leading-relaxed text-white/70">{{ __('site.about.mission_body') }}</p>
                </div>
            </div>

            <div class="reveal relative overflow-hidden rounded-2xl border border-ink/10 bg-white p-8 sm:p-10">
                <span class="grid h-11 w-11 place-items-center rounded-lg bg-sand text-brand">
                    <x-site.icon name="star" class="h-5 w-5" />
                </span>
                <h2 class="mt-5 font-display text-2xl font-semibold text-brand-dark">{{ __('site.about.vision_title') }}</h2>
                <p class="mt-3 text-sm leading-relaxed text-ink/65">{{ __('site.about.vision_body') }}</p>
            </div>
        </div>
    </section>

    {{-- Values --}}
    <section class="bg-white py-20 lg:py-24">
        <div class="site-container">
            <x-site.section-heading
                :eyebrow="__('site.home.about_eyebrow')"
                :title="__('site.about.values_title')"
                align="center" />

            <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-5">
                @foreach (__('site.about.values') as $index => $value)
                    <div class="reveal site-card p-6" style="transition-delay: {{ $index * 60 }}ms">
                        <span class="grid h-10 w-10 place-items-center rounded-lg bg-sand text-brand">
                            <x-site.icon :name="$valueIcons[$index] ?? 'check'" class="h-5 w-5" />
                        </span>
                        <h3 class="mt-4 font-display text-lg font-semibold text-brand-dark">{{ $value['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-ink/60">{{ $value['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Quality --}}
    <section class="site-container py-20 lg:py-24">
        <div class="grid items-center gap-12 lg:grid-cols-2 lg:gap-16">
            <div class="reveal order-2 lg:order-1">
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

            <div class="reveal order-1 lg:order-2">
                <div class="overflow-hidden rounded-2xl">
                    <img src="{{ asset('images/site/photos/process.jpg') }}" alt="{{ __('site.home.cta_image_alt') }}"
                         class="aspect-[4/3] w-full object-cover" loading="lazy">
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
                    {{ __('site.actions.contact_us') }}
                </x-site.button>
            </div>
        </div>
    </section>
@endsection
