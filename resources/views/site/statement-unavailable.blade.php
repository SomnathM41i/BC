@extends('layouts.statement')

@section('title', 'Statement link unavailable')

@section('content')
    <div class="rounded-xl border border-slate-200 bg-white p-6 md:p-8">
        <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">Statement unavailable</p>
        <h1 class="mt-2 text-2xl font-semibold text-slate-900">This link no longer works</h1>
        <p class="mt-3 text-sm leading-relaxed text-slate-600">
            Contractor statements now use a private link that only the owner can share.
            Please ask the owner for the current link to your statement.
        </p>
        <p class="mt-6 text-xs text-slate-400">This page is not indexed and does not show any account information.</p>
    </div>
@endsection
