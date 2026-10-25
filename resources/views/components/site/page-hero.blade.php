@props(['eyebrow' => null, 'title', 'lead' => null])

<section class="relative overflow-hidden bg-brand-dark">
    <div class="absolute inset-0 bg-grid opacity-40" aria-hidden="true"></div>
    <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-brand-red/20 blur-3xl" aria-hidden="true"></div>
    <div class="absolute -bottom-32 -left-16 h-72 w-72 rounded-full bg-gold/10 blur-3xl" aria-hidden="true"></div>

    <div class="site-container relative py-16 sm:py-20 lg:py-24">
        <div class="max-w-3xl">
            @if ($eyebrow)
                <span class="eyebrow eyebrow-light">
                    <span class="h-px w-7 bg-current"></span>
                    {{ $eyebrow }}
                </span>
            @endif

            <h1 class="site-hero-title mt-4 font-display text-4xl font-semibold leading-[1.1] tracking-tight text-white text-balance sm:text-5xl lg:text-[3.4rem]">
                {{ $title }}
            </h1>

            @if ($lead)
                <p class="mt-5 max-w-2xl text-base leading-relaxed text-white/70 sm:text-lg">
                    {{ $lead }}
                </p>
            @endif
        </div>
    </div>
</section>
