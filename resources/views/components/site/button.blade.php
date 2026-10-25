@props(['href' => null, 'variant' => 'primary', 'size' => null, 'icon' => null])

@php
    $classes = match ($variant) {
        'gold' => 'btn-gold',
        'outline' => 'btn-outline',
        'outline-light' => 'btn-outline-light',
        default => 'btn-primary',
    };

    if ($size === 'sm') {
        $classes .= ' px-4 py-2 text-xs';
    }
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        <span>{{ $slot }}</span>
        @if ($icon)
            <x-site.icon :name="$icon" class="h-4 w-4 shrink-0" />
        @endif
    </a>
@else
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $classes]) }}>
        <span>{{ $slot }}</span>
        @if ($icon)
            <x-site.icon :name="$icon" class="h-4 w-4 shrink-0" />
        @endif
    </button>
@endif
