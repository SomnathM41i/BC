@use('App\Support\Site\SiteContent')
@use('App\Support\Site\SiteRoutes')

@props(['project'])

<a href="{{ SiteRoutes::url('portfolio.show', ['portfolio' => $project->slug]) }}"
   class="group block overflow-hidden rounded-2xl border border-ink/10 bg-white shadow-[0_1px_2px_rgba(36,36,36,0.04)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_24px_50px_-28px_rgba(61,11,19,0.55)]">
    <div class="relative aspect-[4/3] overflow-hidden bg-sand">
        @if ($project->coverUrl())
            <img src="{{ $project->coverUrl() }}" alt="{{ $project->titleFor() }}" loading="lazy"
                 class="h-full w-full object-cover transition duration-700 group-hover:scale-[1.04]">
        @else
            <div class="grid h-full place-items-center text-brand/30">
                <x-site.icon name="building" class="h-12 w-12" />
            </div>
        @endif

        <span class="absolute left-4 top-4 rounded-full bg-brand-dark/90 px-3 py-1 text-[0.65rem] font-semibold uppercase tracking-[0.14em] text-white backdrop-blur">
            {{ SiteContent::categoryName($project->category) }}
        </span>

        @if ($project->is_sample)
            <span class="absolute right-4 top-4 rounded-full bg-gold px-3 py-1 text-[0.65rem] font-semibold uppercase tracking-[0.12em] text-brand-dark">
                {{ __('site.common.sample_badge') }}
            </span>
        @endif
    </div>

    <div class="flex items-start justify-between gap-4 p-6">
        <div>
            <h3 class="font-display text-xl font-semibold text-brand-dark transition group-hover:text-brand-red">
                {{ $project->titleFor() }}
            </h3>

            @if ($project->locationFor() || $project->year)
                <p class="mt-1 text-sm text-ink/55">
                    {{ $project->locationFor() }}
                    @if ($project->locationFor() && $project->year)
                        ·
                    @endif
                    {{ $project->year }}
                </p>
            @endif
        </div>

        <span class="mt-1 grid h-9 w-9 shrink-0 place-items-center rounded-full border border-ink/10 text-brand transition group-hover:border-brand group-hover:bg-brand group-hover:text-white">
            <x-site.icon name="arrow-up-right" class="h-4 w-4" />
        </span>
    </div>
</a>
