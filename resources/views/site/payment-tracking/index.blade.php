@extends('layouts.site')
@use('App\Support\Site\SiteRoutes')

@section('title', __('site.payment_tracking.meta.title'))
@section('description', __('site.payment_tracking.meta.description'))
@section('robots', 'noindex, nofollow')

@section('content')
    <x-site.page-hero
        :eyebrow="__('site.payment_tracking.eyebrow')"
        :title="__('site.payment_tracking.title')"
        :lead="__('site.payment_tracking.lead')" />

    <section class="site-container py-14 sm:py-20">
        <div class="mx-auto max-w-5xl">
            @if (session('tracking_error'))
                <div class="mb-8 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
                    {{ session('tracking_error') }}
                </div>
            @endif

            @if (session('tracking_status'))
                <div class="mb-8 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800" role="status">
                    {{ session('tracking_status') }}
                </div>
            @endif

            <div class="max-w-2xl">
                <h2 class="font-display text-2xl font-semibold text-brand-dark sm:text-3xl">
                    {{ __('site.payment_tracking.choose_title') }}
                </h2>
                <p class="mt-2 text-sm leading-relaxed text-ink/65">
                    {{ __('site.payment_tracking.choose_lead') }}
                </p>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                @foreach (['client' => 'building', 'contractor' => 'handshake', 'employee' => 'clipboard'] as $role => $icon)
                    <form method="POST" action="{{ SiteRoutes::url('payment-tracking.category') }}" class="h-full">
                        @csrf
                        <input type="hidden" name="category" value="{{ $role }}">

                        <button type="submit"
                                class="group flex h-full w-full flex-col items-start rounded-2xl border border-ink/10 bg-white p-6 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-brand/30 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-red">
                            <span class="grid h-11 w-11 place-items-center rounded-full bg-brand/10 text-brand">
                                <x-site.icon :name="$icon" class="h-5 w-5" />
                            </span>

                            <span class="mt-4 font-display text-lg font-semibold text-brand-dark">
                                {{ __('site.payment_tracking.roles.'.$role.'.title') }}
                            </span>
                            <span class="mt-2 text-sm leading-relaxed text-ink/60">
                                {{ __('site.payment_tracking.roles.'.$role.'.text') }}
                            </span>
                            <span class="mt-5 inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-brand">
                                {{ __('site.payment_tracking.continue') }}
                                <x-site.icon name="arrow-right" class="h-3.5 w-3.5 transition group-hover:translate-x-0.5" />
                            </span>
                        </button>
                    </form>
                @endforeach
            </div>

            <div class="mt-8 flex items-start gap-3 rounded-xl border border-ink/10 bg-white/60 p-4 text-xs leading-relaxed text-ink/55">
                <x-site.icon name="shield" class="mt-0.5 h-4 w-4 shrink-0 text-brand" />
                <p>{{ __('site.payment_tracking.privacy_note', ['minutes' => config('site.payment_tracking.session_minutes', 30)]) }}</p>
            </div>
        </div>
    </section>
@endsection
