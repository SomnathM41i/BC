@use('App\Support\Site\SiteRoutes')

@props(['service', 'index' => null, 'detailed' => false])

<a href="{{ SiteRoutes::url('services') }}#{{ $service['slug'] }}"
   class="group site-card flex h-full flex-col p-6 transition duration-300 hover:-translate-y-1 hover:border-brand/25 hover:shadow-[0_18px_40px_-24px_rgba(100,21,31,0.5)]">
    <div class="flex items-start justify-between gap-4">
        <span class="grid h-12 w-12 place-items-center rounded-xl bg-sand text-brand transition group-hover:bg-brand group-hover:text-gold">
            <x-site.icon :name="$service['icon']" class="h-6 w-6" />
        </span>

        @if ($index)
            <span class="font-display text-sm font-semibold text-ink/20">
                {{ str_pad((string) $index, 2, '0', STR_PAD_LEFT) }}
            </span>
        @endif
    </div>

    <h3 class="mt-5 font-display text-xl font-semibold text-brand-dark">{{ $service['name'] }}</h3>

    <p class="mt-2 flex-1 text-sm leading-relaxed text-ink/65">
        {{ $detailed ? $service['description'] : $service['short'] }}
    </p>

    @if ($detailed && ! empty($service['benefits']))
        <ul class="mt-4 space-y-2 border-t border-ink/5 pt-4">
            @foreach ($service['benefits'] as $benefit)
                <li class="flex gap-2 text-sm text-ink/70">
                    <x-site.icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-brand-red" />
                    <span>{{ $benefit }}</span>
                </li>
            @endforeach
        </ul>
    @endif

    <span class="mt-5 inline-flex items-center gap-2 text-[0.7rem] font-semibold uppercase tracking-[0.16em] text-brand-red">
        {{ __('site.actions.learn_more') }}
        <x-site.icon name="arrow-right" class="h-4 w-4 transition group-hover:translate-x-1" />
    </span>
</a>
