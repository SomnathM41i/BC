@php
    $statusLabels = [
        'paid' => __('site.payment_tracking.employee.status_paid'),
        'partially_paid' => __('site.payment_tracking.employee.status_partial'),
    ];
@endphp

<div class="space-y-8">
    <div>
        <h2 class="font-display text-xl font-semibold text-brand-dark sm:text-2xl">
            {{ __('site.payment_tracking.employee.summary_title') }}
        </h2>

        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-ink/10 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-ink/50">{{ __('site.payment_tracking.employee.total_paid') }}</p>
                <p class="mt-2 text-2xl font-semibold text-emerald-700">@inr($totalPaid)</p>
            </div>
            <div class="rounded-2xl border border-ink/10 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-ink/50">{{ __('site.payment_tracking.employee.total_due') }}</p>
                <p class="mt-2 text-2xl font-semibold text-brand-dark">@inr($totalDue)</p>
            </div>
            <div class="rounded-2xl border border-ink/10 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-ink/50">{{ __('site.payment_tracking.employee.outstanding_advance') }}</p>
                <p class="mt-2 text-2xl font-semibold text-brand-dark">@inr($outstandingAdvance)</p>
            </div>
        </div>

        <p class="mt-3 text-xs leading-relaxed text-ink/45">
            {{ __('site.payment_tracking.employee.advance_note') }}
        </p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-ink/10 bg-white shadow-sm">
        <div class="border-b border-ink/10 px-5 py-4 sm:px-6">
            <h3 class="font-display text-lg font-semibold text-brand-dark">{{ __('site.payment_tracking.employee.history') }}</h3>
            <p class="mt-1 text-sm text-ink/55">{{ __('site.payment_tracking.employee.history_lead') }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-ink/10 text-sm">
                <thead class="bg-sand/60 text-left text-xs uppercase tracking-wide text-ink/50">
                    <tr>
                        <th class="px-4 py-3 sm:px-5">{{ __('site.payment_tracking.employee.period') }}</th>
                        <th class="px-4 py-3 text-right sm:px-5">{{ __('site.payment_tracking.employee.gross') }}</th>
                        <th class="px-4 py-3 text-right sm:px-5">{{ __('site.payment_tracking.employee.advance_repayment') }}</th>
                        <th class="px-4 py-3 text-right sm:px-5">{{ __('site.payment_tracking.employee.net') }}</th>
                        <th class="px-4 py-3 text-right sm:px-5">{{ __('site.payment_tracking.employee.paid') }}</th>
                        <th class="px-4 py-3 text-right sm:px-5">{{ __('site.payment_tracking.employee.due') }}</th>
                        <th class="px-4 py-3 sm:px-5">{{ __('site.payment_tracking.employee.payment_date') }}</th>
                        <th class="px-4 py-3 sm:px-5">{{ __('site.payment_tracking.employee.status') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ink/10">
                    @forelse ($payments as $payment)
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3 text-ink/70 sm:px-5">
                                {{ $payment->period_start?->format('d M') }} – {{ $payment->period_end?->format('d M Y') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-ink/70 sm:px-5">@inr($payment->gross_salary)</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-ink/70 sm:px-5">@inr($payment->advance_repayment)</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right font-medium text-ink sm:px-5">@inr($payment->net_salary_payable)</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-emerald-700 sm:px-5">@inr($payment->amount_paid)</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-ink/70 sm:px-5">@inr($payment->remaining_salary_due)</td>
                            <td class="whitespace-nowrap px-4 py-3 text-ink/60 sm:px-5">
                                {{ $payment->payment_date?->format('d M Y') ?? '—' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 sm:px-5">
                                <span @class([
                                    'rounded-full px-2.5 py-1 text-xs font-medium',
                                    'bg-emerald-100 text-emerald-800' => $payment->status === 'paid',
                                    'bg-amber-100 text-amber-800' => $payment->status !== 'paid',
                                ])>
                                    {{ $statusLabels[$payment->status] ?? $payment->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-sm text-ink/50">
                                {{ __('site.payment_tracking.employee.no_records') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
