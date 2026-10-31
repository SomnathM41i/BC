<span @class([
    'rounded-full px-2.5 py-1 text-xs font-medium',
    'bg-emerald-100 text-emerald-800' => $status === 'present',
    'bg-amber-100 text-amber-800' => $status === 'absent',
    'bg-sand/60 text-ink/60' => ! $status,
])>
    {{ __('site.payment_tracking.employee.attendance_'.(($level ?? null) ?: $status ?: 'not_marked')) }}
</span>
