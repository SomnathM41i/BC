@props([
    'eyebrow' => null,
    'title' => null,
    'lead' => null,
    'tone' => 'light',
    'align' => 'left',
])

<div @class([
    'reveal',
    'mx-auto max-w-2xl text-center' => $align === 'center',
    'max-w-2xl' => $align !== 'center',
])>
    @if ($eyebrow)
        <span @class(['eyebrow', 'eyebrow-light' => $tone === 'dark'])>
            <span class="h-px w-7 bg-current"></span>
            {{ $eyebrow }}
        </span>
    @endif

    @if ($title)
        <h2 @class(['display-title mt-4 text-balance', 'display-title-light' => $tone === 'dark'])>
            {{ $title }}
        </h2>
    @endif

    @if ($lead)
        <p @class(['lead-copy mt-4 text-balance', 'text-white/70' => $tone === 'dark'])>
            {{ $lead }}
        </p>
    @endif
</div>
