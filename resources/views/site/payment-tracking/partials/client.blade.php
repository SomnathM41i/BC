@use('App\Support\Money')

@php
    $statusKey = 'site.payment_tracking.client.status_'.$selectedProject->status->value;
    $projectRemaining = $selectedProject->remainingAgainstAgreed();
    $projectHasExcess = Money::lt($projectRemaining, '0');
@endphp

<div class="space-y-8">
    {{-- Overall summary across every project linked to this number --}}
    <div>
        <h2 class="font-display text-xl font-semibold text-brand-dark sm:text-2xl">
            {{ __('site.payment_tracking.client.summary_title') }}
        </h2>
        <p class="mt-1 text-sm text-ink/55">{{ __('site.payment_tracking.client.overall_note') }}</p>

        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-ink/10 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-ink/50">{{ __('site.payment_tracking.client.agreed') }}</p>
                <p class="mt-2 text-2xl font-semibold text-brand-dark">@inr($totalAgreed)</p>
            </div>
            <div class="rounded-2xl border border-ink/10 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-ink/50">{{ __('site.payment_tracking.client.received') }}</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-700">@inr($totalReceived)</p>
            </div>
            <div class="rounded-2xl border border-ink/10 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-ink/50">
                    {{ Money::lt($totalRemaining, '0') ? __('site.payment_tracking.client.excess') : __('site.payment_tracking.client.remaining') }}
                </p>
                <p @class(['mt-2 text-2xl font-semibold', 'text-amber-700' => Money::lt($totalRemaining, '0'), 'text-brand' => ! Money::lt($totalRemaining, '0')])>
                    @inr(Money::lt($totalRemaining, '0') ? Money::negate($totalRemaining) : $totalRemaining)
                </p>
            </div>
        </div>
    </div>

    @if ($projects->count() > 1)
        <form method="POST" action="{{ \App\Support\Site\SiteRoutes::url('payment-tracking.project') }}"
              class="flex flex-col gap-3 rounded-2xl border border-ink/10 bg-white p-4 shadow-sm sm:flex-row sm:items-end">
            @csrf
            <div class="flex-1">
                <label for="project_id" class="site-label">{{ __('site.payment_tracking.client.select_project') }}</label>
                <select id="project_id" name="project_id" class="site-input">
                    @foreach ($projects as $project)
                        <option value="{{ $project->id }}" @selected($project->id === $selectedProject->id)>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-primary sm:w-auto">
                {{ __('site.payment_tracking.client.view') }}
            </button>
        </form>
    @endif

    <div class="overflow-hidden rounded-2xl border border-ink/10 bg-white shadow-sm">
        <div class="border-b border-ink/10 px-5 py-4 sm:px-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="font-display text-lg font-semibold text-brand-dark">{{ $selectedProject->name }}</h3>
                    <p class="mt-1 text-sm text-ink/55">
                        {{ $selectedProject->location ?: __('site.payment_tracking.client.no_location') }}
                    </p>
                </div>
                <span class="rounded-full bg-brand/10 px-3 py-1 text-xs font-semibold text-brand">
                    {{ __($statusKey) }}
                </span>
            </div>

            <dl class="mt-4 grid grid-cols-1 gap-3 text-sm sm:grid-cols-3">
                <div class="rounded-xl bg-sand/60 p-3">
                    <dt class="text-xs text-ink/50">{{ __('site.payment_tracking.client.agreed') }}</dt>
                    <dd class="mt-1 font-semibold text-ink">@inr($selectedProject->agreed_amount)</dd>
                </div>
                <div class="rounded-xl bg-sand/60 p-3">
                    <dt class="text-xs text-ink/50">{{ __('site.payment_tracking.client.received') }}</dt>
                    <dd class="mt-1 font-semibold text-emerald-700">@inr($selectedProject->totalReceived())</dd>
                </div>
                <div class="rounded-xl bg-sand/60 p-3">
                    <dt class="text-xs text-ink/50">
                        {{ $projectHasExcess ? __('site.payment_tracking.client.excess') : __('site.payment_tracking.client.remaining') }}
                    </dt>
                    <dd class="mt-1 font-semibold text-brand">
                        @inr($projectHasExcess ? Money::negate($projectRemaining) : $projectRemaining)
                    </dd>
                </div>
            </dl>
        </div>

        <div class="px-5 py-4 sm:px-6">
            <h4 class="font-semibold text-ink">{{ __('site.payment_tracking.client.receipts') }}</h4>
            <p class="mt-1 text-xs text-ink/50">
                {{ __('site.payment_tracking.client.receipts_lead', ['project' => $selectedProject->name]) }}
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-ink/10 text-sm">
                <thead class="bg-sand/60 text-left text-xs uppercase tracking-wide text-ink/50">
                    <tr>
                        <th class="px-5 py-3 sm:px-6">{{ __('site.payment_tracking.client.date') }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ __('site.payment_tracking.client.description') }}</th>
                        <th class="px-5 py-3 text-right sm:px-6">{{ __('site.payment_tracking.client.amount') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink/10">
                    @forelse ($receipts as $receipt)
                        <tr>
                            <td class="whitespace-nowrap px-5 py-3 text-ink/70 sm:px-6">
                                {{ $receipt->transaction_date?->format('d M Y') ?? '—' }}
                            </td>
                            <td class="px-5 py-3 text-ink/70 sm:px-6">
                                {{ $receipt->description ?: '—' }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-right font-semibold text-ink sm:px-6">
                                @inr($receipt->amount)
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-8 text-center text-sm text-ink/50 sm:px-6">
                                {{ __('site.payment_tracking.client.no_receipts') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($receipts->isNotEmpty())
                    <tfoot class="bg-sand/60">
                        <tr>
                            <td colspan="2" class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-ink/50 sm:px-6">
                                {{ __('site.payment_tracking.client.received') }}
                            </td>
                            <td class="px-5 py-3 text-right font-semibold text-ink sm:px-6">
                                @inr($selectedProject->totalReceived())
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
