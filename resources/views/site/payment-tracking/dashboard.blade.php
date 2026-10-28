@extends('layouts.site')
@use('App\Support\PhoneNumber')
@use('App\Support\Site\SiteRoutes')

@section('title', __('site.payment_tracking.dashboard.title'))
@section('description', __('site.payment_tracking.meta.description'))
@section('robots', 'noindex, nofollow')

@php
    $name = match ($category) {
        'client' => ($projects->first()->client_name ?? null) ?: ($projects->first()->name ?? null),
        'contractor' => $contractor->name ?? null,
        'employee' => $employee->full_name ?? null,
        default => null,
    };

    $mobile = match ($category) {
        'client' => ($projects->first()->client_mobile ?? null) ?: ($projects->first()->client_mobile_normalized ?? null),
        'contractor' => $contractor->phone ?? null,
        'employee' => $employee->phone_number ?? null,
        default => null,
    };
    $mobileDisplay = PhoneNumber::display($mobile);

    // "What he is": contractor trade, or the employee's staff ID.
    $designation = match ($category) {
        'contractor' => $contractor->type?->name,
        'employee' => $employee->employee_id,
        default => null,
    };

    $expiresAt = \Illuminate\Support\Carbon::createFromTimestamp((int) ($tracking['verified_at'] ?? now()->timestamp))
        ->addMinutes((int) config('site.payment_tracking.session_minutes', 30));
@endphp

@section('content')
    <section class="border-b border-ink/10 bg-white">
        <div class="site-container py-8 sm:py-10">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 class="font-display text-2xl font-semibold text-brand-dark sm:text-3xl">
                        {{ __('site.payment_tracking.dashboard.title') }}
                    </h1>

                    <div class="mt-4 flex items-start gap-3">
                        <span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-emerald-100 text-emerald-700">
                            <x-site.icon name="shield" class="h-5 w-5" />
                        </span>

                        <div>
                            <p class="text-base font-semibold text-ink">{{ $name }}</p>

                            <p class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-ink/60">
                                <span class="font-medium text-brand">
                                    {{ __('site.payment_tracking.roles.'.$category.'.title') }}
                                </span>

                                @if ($designation)
                                    <span>{{ $designation }}</span>
                                @endif

                                @if ($mobileDisplay)
                                    <span class="inline-flex items-center gap-1">
                                        <x-site.icon name="phone" class="h-3.5 w-3.5" />
                                        {{ $mobileDisplay }}
                                    </span>
                                @endif
                            </p>

                            <p class="mt-1 text-xs text-ink/45">
                                {{ __('site.payment_tracking.dashboard.verified_until', ['time' => $expiresAt->format('h:i A')]) }}
                            </p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ SiteRoutes::url('payment-tracking.logout') }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex min-h-10 items-center justify-center rounded-full border border-ink/15 px-4 py-2 text-xs font-semibold text-ink/70 transition hover:border-brand hover:text-brand">
                        {{ __('site.payment_tracking.dashboard.logout') }}
                    </button>
                </form>
            </div>
        </div>
    </section>

    <section class="site-container py-10 sm:py-12">
        @if (session('tracking_error'))
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
                {{ session('tracking_error') }}
            </div>
        @endif

        @if (session('tracking_status'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800" role="status">
                {{ session('tracking_status') }}
            </div>
        @endif

        @include('site.payment-tracking.partials.'.$category)

        <p class="mt-8 flex items-start gap-2 text-xs leading-relaxed text-ink/45">
            <x-site.icon name="shield" class="mt-0.5 h-3.5 w-3.5 shrink-0" />
            <span>{{ __('site.payment_tracking.dashboard.privacy') }}</span>
        </p>
    </section>
@endsection
