@php
    $projectCount = count($report['rows']);
@endphp

<div class="space-y-8">
    <div>
        <h2 class="font-display text-xl font-semibold text-brand-dark sm:text-2xl">
            {{ __('site.payment_tracking.contractor.summary_title') }}
        </h2>

        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="rounded-2xl border border-ink/10 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-ink/50">{{ __('site.payment_tracking.contractor.total_given') }}</p>
                <p class="mt-2 text-2xl font-semibold text-brand-dark">@inr($report['total'])</p>
            </div>
            <div class="rounded-2xl border border-ink/10 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-ink/50">{{ __('site.payment_tracking.contractor.projects_count') }}</p>
                <p class="mt-2 text-2xl font-semibold text-brand-dark">{{ $projectCount }}</p>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-ink/10 bg-white shadow-sm">
        <div class="border-b border-ink/10 px-5 py-4 sm:px-6">
            <h3 class="font-display text-lg font-semibold text-brand-dark">{{ __('site.payment_tracking.contractor.by_project') }}</h3>
            <p class="mt-1 text-sm text-ink/55">{{ __('site.payment_tracking.contractor.by_project_lead') }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-ink/10 text-sm">
                <thead class="bg-sand/60 text-left text-xs uppercase tracking-wide text-ink/50">
                    <tr>
                        <th class="px-5 py-3 sm:px-6">{{ __('site.payment_tracking.contractor.project') }}</th>
                        <th class="px-5 py-3 text-center sm:px-6">{{ __('site.payment_tracking.contractor.payments') }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ __('site.payment_tracking.contractor.last_payment') }}</th>
                        <th class="px-5 py-3 text-right sm:px-6">{{ __('site.payment_tracking.contractor.amount_given') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink/10">
                    @forelse ($report['rows'] as $row)
                        <tr>
                            <td class="px-5 py-3 font-medium text-ink sm:px-6">{{ $row['project']->name }}</td>
                            <td class="px-5 py-3 text-center text-ink/60 sm:px-6">{{ $row['payments'] }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-ink/60 sm:px-6">
                                {{ $row['last_date'] ? \Illuminate\Support\Carbon::parse($row['last_date'])->format('d M Y') : '—' }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-right font-semibold text-ink sm:px-6">
                                @inr($row['total'])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-sm text-ink/50 sm:px-6">
                                {{ __('site.payment_tracking.contractor.no_records') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($projectCount)
                    <tfoot class="bg-sand/60">
                        <tr>
                            <td colspan="3" class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-ink/50 sm:px-6">
                                {{ __('site.payment_tracking.contractor.total_given') }}
                            </td>
                            <td class="px-5 py-3 text-right font-semibold text-ink sm:px-6">@inr($report['total'])</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-ink/10 bg-white shadow-sm">
        <div class="border-b border-ink/10 px-5 py-4 sm:px-6">
            <h3 class="font-display text-lg font-semibold text-brand-dark">{{ __('site.payment_tracking.contractor.history') }}</h3>
            <p class="mt-1 text-sm text-ink/55">{{ __('site.payment_tracking.contractor.history_lead') }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-ink/10 text-sm">
                <thead class="bg-sand/60 text-left text-xs uppercase tracking-wide text-ink/50">
                    <tr>
                        <th class="px-5 py-3 sm:px-6">{{ __('site.payment_tracking.contractor.date') }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ __('site.payment_tracking.contractor.project') }}</th>
                        <th class="px-5 py-3 sm:px-6">{{ __('site.payment_tracking.contractor.description') }}</th>
                        <th class="px-5 py-3 text-right sm:px-6">{{ __('site.payment_tracking.contractor.amount') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink/10">
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td class="whitespace-nowrap px-5 py-3 text-ink/70 sm:px-6">
                                {{ $transaction->transaction_date?->format('d M Y') ?? '—' }}
                            </td>
                            <td class="px-5 py-3 text-ink/70 sm:px-6">{{ $transaction->project?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-ink/70 sm:px-6">{{ $transaction->description ?: '—' }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-right font-semibold text-ink sm:px-6">
                                @inr($transaction->amount)
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-sm text-ink/50 sm:px-6">
                                {{ __('site.payment_tracking.contractor.no_history') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-xs leading-relaxed text-ink/45">{{ __('site.payment_tracking.contractor.note') }}</p>
</div>
