@extends('layouts.site')
@use('App\Support\Site\SiteRoutes')

@section('title', __('site.payment_tracking.otp.title'))
@section('description', __('site.payment_tracking.meta.description'))
@section('robots', 'noindex, nofollow')

@section('content')
    <x-site.page-hero
        :eyebrow="__('site.payment_tracking.eyebrow')"
        :title="__('site.payment_tracking.otp.title')"
        :lead="__('site.payment_tracking.otp.lead', ['mobile' => $maskedMobile])" />

    <section class="site-container py-14 sm:py-20">
        <div class="mx-auto max-w-lg">
            @if (session('tracking_error'))
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
                    {{ session('tracking_error') }}
                </div>
            @endif

            <div class="site-card p-6 sm:p-8">
                <form method="POST" action="{{ SiteRoutes::url('payment-tracking.otp.store') }}" class="space-y-5" novalidate>
                    @csrf

                    <div>
                        <label for="code" class="site-label">{{ __('site.payment_tracking.otp.label') }}</label>
                        <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code"
                               maxlength="6" required autofocus
                               aria-invalid="{{ $errors->has('code') ? 'true' : 'false' }}"
                               @class(['site-input', 'border-red-300 focus:border-red-400 focus:ring-red-100' => $errors->has('code')])>

                        @error('code')
                            <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="btn-primary w-full">
                        <span>{{ __('site.payment_tracking.otp.submit') }}</span>
                        <x-site.icon name="arrow-right" class="h-4 w-4" />
                    </button>
                </form>

                <form method="POST" action="{{ SiteRoutes::url('payment-tracking.otp.resend') }}" class="mt-5">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-brand underline">
                        {{ __('site.payment_tracking.otp.resend') }}
                    </button>
                </form>

                <a href="{{ SiteRoutes::url('payment-tracking.verify') }}"
                   class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-brand underline">
                    <x-site.icon name="arrow-left" class="h-3.5 w-3.5" />
                    {{ __('site.payment_tracking.otp.change_number') }}
                </a>
            </div>

            <div class="mt-6 flex items-start gap-3 rounded-xl border border-ink/10 bg-white/60 p-4 text-xs leading-relaxed text-ink/55">
                <x-site.icon name="shield" class="mt-0.5 h-4 w-4 shrink-0 text-brand" />
                <p>{{ __('site.payment_tracking.otp.help') }}</p>
            </div>
        </div>
    </section>
@endsection
