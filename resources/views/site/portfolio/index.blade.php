@extends('layouts.site')
@use('App\Support\Site\SiteRoutes')

@section('title', __('site.meta.portfolio.title'))
@section('description', __('site.meta.portfolio.description'))

@section('content')
    <x-site.page-hero
        :eyebrow="__('site.portfolio.banner_eyebrow')"
        :title="__('site.portfolio.banner_title')"
        :lead="__('site.portfolio.banner_lead')" />

    <section class="site-container py-16 lg:py-20">
        <div class="flex flex-col gap-6 border-b border-ink/10 pb-8 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-xl">
                <p class="text-sm leading-relaxed text-ink/65">{{ __('site.portfolio.intro') }}</p>
                <p class="mt-2 text-xs leading-relaxed text-ink/45">{{ __('site.portfolio.visualisation_note') }}</p>
            </div>

            <nav class="flex flex-wrap gap-2 lg:max-w-[26rem]" aria-label="{{ __('site.portfolio.filter_label') }}">
                <a href="{{ SiteRoutes::url('portfolio.index') }}"
                   @if ($category === '') aria-current="page" @endif
                   @class(['chip', 'chip-active' => $category === ''])>
                    {{ __('site.common.all') }}
                </a>

                @foreach ($categories as $item)
                    <a href="{{ SiteRoutes::url('portfolio.index', ['category' => $item['key']]) }}"
                       @if ($category === $item['key']) aria-current="page" @endif
                       @class(['chip', 'chip-active' => $category === $item['key']])>
                        {{ $item['name'] }}
                    </a>
                @endforeach
            </nav>
        </div>

        @if ($projects->isNotEmpty())
            <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($projects as $project)
                    <div class="reveal">
                        <x-site.project-card :project="$project" />
                    </div>
                @endforeach
            </div>
        @else
            <div class="mt-10 rounded-2xl border border-dashed border-ink/15 bg-white/60 p-12 text-center">
                <p class="font-display text-xl font-semibold text-brand-dark">{{ __('site.portfolio.empty_title') }}</p>
                <p class="mx-auto mt-2 max-w-md text-sm text-ink/55">{{ __('site.portfolio.empty_text') }}</p>
                <div class="mt-6">
                    <x-site.button :href="SiteRoutes::url('contact')" size="sm">
                        {{ __('site.actions.contact_us') }}
                    </x-site.button>
                </div>
            </div>
        @endif
    </section>

    <section class="bg-brand-dark">
        <div class="site-container flex flex-col items-start gap-6 py-14 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-display text-2xl font-semibold text-white sm:text-3xl">{{ __('site.home.cta_title') }}</h2>
                <p class="mt-2 max-w-xl text-sm text-white/65">{{ __('site.home.cta_text') }}</p>
            </div>

            <div class="flex shrink-0 flex-wrap gap-3">
                <x-site.button :href="SiteRoutes::url('contact')" variant="gold" icon="arrow-right">
                    {{ __('site.actions.contact_us') }}
                </x-site.button>
            </div>
        </div>
    </section>
@endsection
