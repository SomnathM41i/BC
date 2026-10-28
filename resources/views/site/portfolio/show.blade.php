@extends('layouts.site')
@use('App\Support\Site\SiteContent')
@use('App\Support\Site\SiteRoutes')

@section('title', $project->titleFor())
@section('description', \Illuminate\Support\Str::limit($project->summaryFor(), 155))

@if ($project->coverUrl())
    @push('head')
        <meta property="og:image" content="{{ $project->coverUrl() }}">
    @endpush
@endif

@section('content')
    <section class="bg-brand-dark">
        <div class="site-container py-12 sm:py-16">
            <a href="{{ SiteRoutes::url('portfolio.index') }}"
               class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] text-white/60 transition hover:text-gold">
                <x-site.icon name="arrow-left" class="h-4 w-4" />
                {{ __('site.actions.back_to_portfolio') }}
            </a>

            <div class="mt-6 flex flex-wrap items-center gap-2">
                <span class="rounded-full bg-white/10 px-3 py-1 text-[0.65rem] font-semibold uppercase tracking-[0.14em] text-gold">
                    {{ SiteContent::categoryName($project->category) }}
                </span>

                <span class="rounded-full border border-white/15 px-3 py-1 text-[0.65rem] font-semibold uppercase tracking-[0.14em] text-white/55">
                    {{ __('site.portfolio.detail.visualisation_note') }}
                </span>
            </div>

            <h1 class="mt-4 max-w-3xl font-display text-4xl font-semibold leading-[1.1] text-white text-balance sm:text-5xl">
                {{ $project->titleFor() }}
            </h1>

            <p class="mt-4 max-w-2xl text-base leading-relaxed text-white/70">{{ $project->summaryFor() }}</p>
        </div>
    </section>

    @if ($project->coverUrl())
        <div class="site-container -mt-1">
            <div class="overflow-hidden rounded-b-2xl">
                <img src="{{ $project->coverUrl() }}"
                     alt="{{ $project->titleFor() }} — {{ __('site.portfolio.detail.visualisation_note') }}"
                     class="max-h-[520px] w-full object-cover" decoding="async">
            </div>
        </div>
    @endif

    <section class="site-container py-16 lg:py-20">
        <div class="grid gap-12 lg:grid-cols-[1.6fr_0.9fr] lg:gap-16">
            <div>
                <h2 class="font-display text-2xl font-semibold text-brand-dark">{{ __('site.portfolio.detail.overview') }}</h2>

                <div class="mt-4 space-y-4 text-sm leading-relaxed text-ink/70 sm:text-base">
                    @foreach (preg_split('/\R{2,}/', $project->descriptionFor()) as $paragraph)
                        <p>{!! nl2br(e(trim($paragraph))) !!}</p>
                    @endforeach
                </div>

                @if (! empty($relatedServices))
                    <div class="mt-10 border-t border-ink/10 pt-8">
                        <h2 class="font-display text-xl font-semibold text-brand-dark">{{ __('site.portfolio.detail.related_services') }}</h2>

                        <ul class="mt-4 flex flex-wrap gap-2">
                            @foreach ($relatedServices as $service)
                                <li>
                                    <a href="{{ SiteRoutes::url('services') }}#{{ $service['slug'] }}" class="chip">
                                        {{ $service['name'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <aside class="space-y-6">
                <div class="site-card p-6">
                    <dl class="space-y-4 text-sm">
                        <div>
                            <dt class="text-[0.65rem] font-semibold uppercase tracking-[0.18em] text-ink/45">{{ __('site.portfolio.detail.category') }}</dt>
                            <dd class="mt-1 font-medium text-brand-dark">{{ SiteContent::categoryName($project->category) }}</dd>
                        </div>

                        @if ($project->locationFor())
                            <div>
                                <dt class="text-[0.65rem] font-semibold uppercase tracking-[0.18em] text-ink/45">{{ __('site.portfolio.detail.location') }}</dt>
                                <dd class="mt-1 font-medium text-brand-dark">{{ $project->locationFor() }}</dd>
                            </div>
                        @endif

                        @if ($project->year)
                            <div>
                                <dt class="text-[0.65rem] font-semibold uppercase tracking-[0.18em] text-ink/45">{{ __('site.portfolio.detail.year') }}</dt>
                                <dd class="mt-1 font-medium text-brand-dark">{{ $project->year }}</dd>
                            </div>
                        @endif
                    </dl>

                    <div class="mt-6 border-t border-ink/10 pt-5">
                        <x-site.button :href="SiteRoutes::url('contact')" size="sm" class="w-full">
                            {{ __('site.actions.discuss_project') }}
                        </x-site.button>
                    </div>
                </div>

                <div class="rounded-2xl bg-brand-dark p-6 text-white">
                    <p class="text-[0.65rem] font-semibold uppercase tracking-[0.18em] text-gold">{{ __('site.contact.phone_label') }}</p>
                    <a href="{{ $site['phones'][0]['tel'] }}" class="mt-2 block font-display text-xl font-semibold text-white">
                        {{ $site['phones'][0]['display'] }}
                    </a>
                    <a href="{{ $site['whatsapp_url'] }}" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-gold transition hover:text-white">
                        <x-site.icon name="whatsapp" class="h-4 w-4" />
                        {{ __('site.actions.whatsapp') }}
                    </a>
                </div>
            </aside>
        </div>
    </section>

    @if (count($project->galleryUrls()) > 0)
        <section class="bg-white py-16 lg:py-20">
            <div class="site-container">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <h2 class="font-display text-2xl font-semibold text-brand-dark">{{ __('site.portfolio.detail.gallery') }}</h2>
                    <p class="text-xs text-ink/45">{{ __('site.portfolio.visualisation_note') }}</p>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($project->galleryUrls() as $image)
                        <div class="overflow-hidden rounded-xl bg-sand">
                            <img src="{{ $image }}"
                                 alt="{{ $project->titleFor() }} — {{ __('site.portfolio.detail.gallery') }} {{ $loop->iteration }}"
                                 loading="lazy" decoding="async"
                                 class="aspect-[4/3] w-full object-cover transition duration-700 hover:scale-[1.03]">
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($related->isNotEmpty())
        <section class="site-container py-16 lg:py-20">
            <div class="flex items-end justify-between gap-4">
                <h2 class="font-display text-2xl font-semibold text-brand-dark">{{ __('site.portfolio.detail.related') }}</h2>
                <a href="{{ SiteRoutes::url('portfolio.index') }}"
                   class="text-xs font-semibold uppercase tracking-[0.16em] text-brand transition hover:text-brand-red">
                    {{ __('site.actions.view_all_projects') }}
                </a>
            </div>

            <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($related as $item)
                    <div class="reveal">
                        <x-site.project-card :project="$item" />
                    </div>
                @endforeach
            </div>
        </section>
    @endif
@endsection
