@extends('layouts.site')
@use('App\Support\Site\SiteContent')
@use('App\Support\Site\SiteRoutes')

@section('title', $project->titleFor())
@section('description', \Illuminate\Support\Str::limit($project->summaryFor(), 155))

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

                @if ($project->is_sample)
                    <span class="rounded-full bg-gold px-3 py-1 text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-brand-dark">
                        {{ __('site.common.sample_badge') }}
                    </span>
                @endif
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
                <img src="{{ $project->coverUrl() }}" alt="{{ $project->titleFor() }}"
                     class="max-h-[520px] w-full object-cover">
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

                @if ($project->is_sample)
                    <p class="mt-8 rounded-xl border border-gold/40 bg-gold/10 p-5 text-xs leading-relaxed text-ink/60">
                        {{ __('site.portfolio.detail.sample_note') }}
                    </p>
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
                            {{ __('site.actions.request_quote') }}
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
        <section class="bg-white py-16">
            <div class="site-container">
                <h2 class="font-display text-2xl font-semibold text-brand-dark">{{ __('site.portfolio.detail.gallery') }}</h2>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($project->galleryUrls() as $image)
                        <div class="overflow-hidden rounded-xl">
                            <img src="{{ $image }}" alt="{{ $project->titleFor() }}" loading="lazy"
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
