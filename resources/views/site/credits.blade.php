@extends('layouts.site')

@section('title', __('site.meta.credits.title'))
@section('description', __('site.meta.credits.description'))

@section('content')
    <x-site.page-hero
        :eyebrow="__('site.credits.title')"
        :title="__('site.credits.title')"
        :lead="__('site.credits.lead')" />

    <section class="site-container py-16 lg:py-20">
        <div class="max-w-3xl">
            <p class="text-base leading-relaxed text-ink/70">{{ __('site.credits.intro') }}</p>
            <p class="mt-3 text-sm leading-relaxed text-ink/50">{{ __('site.credits.note') }}</p>
        </div>

        @if (empty($credits))
            <p class="mt-10 rounded-xl border border-ink/10 bg-white p-6 text-sm text-ink/55">{{ __('site.credits.empty') }}</p>
        @else
            <ul class="mt-10 grid gap-4 sm:grid-cols-2">
                @foreach ($credits as $credit)
                    <li class="site-card flex gap-4 p-4">
                        <img src="{{ asset($credit['local_path']) }}" alt="{{ $credit['title'] }}"
                             class="h-20 w-24 shrink-0 rounded-lg object-cover" loading="lazy" decoding="async">

                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-brand-dark">{{ $credit['title'] }}</p>
                            <p class="mt-1 text-xs text-ink/60">{{ __('site.credits.author') }}: {{ $credit['author'] }}</p>
                            <p class="text-xs text-ink/60">
                                {{ __('site.credits.licence') }}:
                                @if (! empty($credit['license_url']))
                                    <a href="{{ $credit['license_url'] }}" target="_blank" rel="noopener nofollow"
                                       class="underline decoration-ink/20 underline-offset-2 hover:text-brand">{{ $credit['license'] }}</a>
                                @else
                                    {{ $credit['license'] }}
                                @endif
                            </p>
                            @if (! empty($credit['page']))
                                <a href="{{ $credit['page'] }}" target="_blank" rel="noopener nofollow"
                                   class="mt-2 inline-block text-xs font-semibold text-brand underline decoration-brand/30 underline-offset-2 hover:text-brand-dark">
                                    {{ __('site.credits.view_source') }}
                                </a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
@endsection
