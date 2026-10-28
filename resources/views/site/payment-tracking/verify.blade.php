@extends('layouts.site')
@use('App\Support\Site\SiteRoutes')

@section('title', __('site.payment_tracking.meta.title'))
@section('description', __('site.payment_tracking.meta.description'))
@section('robots', 'noindex, nofollow')

@section('content')
    <x-site.page-hero
        :eyebrow="__('site.payment_tracking.eyebrow')"
        :title="__('site.payment_tracking.verify.title')"
        :lead="__('site.payment_tracking.verify.lead')" />

    <section class="site-container py-14 sm:py-20">
        <div class="mx-auto max-w-lg">
            @if (session('tracking_error'))
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
                    {{ session('tracking_error') }}
                </div>
            @endif

            <div class="site-card p-6 sm:p-8">
                <p class="text-xs font-semibold uppercase tracking-wider text-ink/50">
                    {{ __('site.payment_tracking.verify.for', ['role' => __('site.payment_tracking.roles.'.$category.'.title')]) }}
                </p>

                <form method="POST" action="{{ SiteRoutes::url('payment-tracking.verify.store') }}" class="mt-5 space-y-5" novalidate>
                    @csrf

                    <div>
                        <label for="mobile" class="site-label">{{ __('site.payment_tracking.verify.label') }}</label>
                        <input id="mobile" name="mobile" type="tel" inputmode="numeric" autocomplete="tel"
                               value="{{ old('mobile') }}" placeholder="{{ __('site.payment_tracking.verify.placeholder') }}"
                               maxlength="20" required autofocus
                               aria-invalid="{{ $errors->has('mobile') ? 'true' : 'false' }}"
                               aria-describedby="mobile-help mobile-error"
                               @class(['site-input', 'border-red-300 focus:border-red-400 focus:ring-red-100' => $errors->has('mobile')])>

                        <p id="mobile-help" class="mt-2 text-xs leading-relaxed text-ink/50">
                            {{ __('site.payment_tracking.verify.help') }}
                        </p>

                        @error('mobile')
                            <p id="mobile-error" class="mt-1 text-xs text-red-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="btn-primary w-full">
                        <span>{{ __('site.payment_tracking.verify.submit') }}</span>
                        <x-site.icon name="arrow-right" class="h-4 w-4" />
                    </button>
                </form>

                <a href="{{ SiteRoutes::url('payment-tracking.index') }}"
                   class="mt-6 inline-flex items-center gap-1.5 text-sm font-medium text-brand underline">
                    <x-site.icon name="arrow-left" class="h-3.5 w-3.5" />
                    {{ __('site.payment_tracking.verify.back') }}
                </a>
            </div>

            <div class="mt-6 flex items-start gap-3 rounded-xl border border-ink/10 bg-white/60 p-4 text-xs leading-relaxed text-ink/55">
                <x-site.icon name="shield" class="mt-0.5 h-4 w-4 shrink-0 text-brand" />
                <p>{{ __('site.payment_tracking.privacy_note', ['minutes' => config('site.payment_tracking.session_minutes', 30)]) }}</p>
            </div>
        </div>
    </section>
@endsection
