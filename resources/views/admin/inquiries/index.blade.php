@extends('layouts.admin')
@use('App\Models\Inquiry')

@section('title', 'Enquiries')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">Website Enquiries</h1>
        <p class="text-sm text-slate-500">Messages sent through the contact form on the public website.</p>
    </div>

    <div class="space-y-4">
        @forelse ($inquiries as $inquiry)
            <div class="rounded-xl bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="font-semibold text-slate-900">
                            {{ $inquiry->name }}
                            @if ($inquiry->status === Inquiry::STATUS_NEW)
                                <span class="ml-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">New</span>
                            @endif
                        </p>
                        <p class="mt-1 text-sm text-slate-600">
                            <a href="tel:{{ $inquiry->phone }}" class="font-medium hover:underline">{{ $inquiry->phone }}</a>
                            @if ($inquiry->email)
                                · <a href="mailto:{{ $inquiry->email }}" class="hover:underline">{{ $inquiry->email }}</a>
                            @endif
                        </p>
                        <p class="mt-1 text-xs text-slate-500">{{ $inquiry->created_at->format('d-m-Y h:i A') }}</p>
                    </div>

                    <form method="POST" action="{{ route('admin.inquiries.status', $inquiry) }}">
                        @csrf
                        @if ($inquiry->status === Inquiry::STATUS_NEW)
                            <button type="submit" name="status" value="{{ Inquiry::STATUS_READ }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50">Mark as read</button>
                        @elseif ($inquiry->status === Inquiry::STATUS_READ)
                            <button type="submit" name="status" value="{{ Inquiry::STATUS_CLOSED }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50">Close</button>
                        @else
                            <button type="submit" name="status" value="{{ Inquiry::STATUS_READ }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50">Reopen</button>
                        @endif
                    </form>
                </div>

                <dl class="mt-3 flex flex-wrap gap-x-6 gap-y-1 text-sm text-slate-600">
                    @if ($inquiry->service)
                        <div>
                            <dt class="inline text-slate-400">Service:</dt>
                            <dd class="inline">{{ $inquiry->service === 'other' ? 'Other / not sure yet' : __('site.services.items.'.$inquiry->service.'.name', [], 'en') }}</dd>
                        </div>
                    @endif
                    @if ($inquiry->location)
                        <div>
                            <dt class="inline text-slate-400">Location:</dt>
                            <dd class="inline">{{ $inquiry->location }}</dd>
                        </div>
                    @endif
                    @if ($inquiry->budget)
                        <div>
                            <dt class="inline text-slate-400">Budget:</dt>
                            <dd class="inline">{{ $inquiry->budget }}</dd>
                        </div>
                    @endif
                </dl>

                <p class="mt-3 whitespace-pre-line text-sm text-slate-700">{{ $inquiry->message }}</p>
            </div>
        @empty
            <div class="rounded-xl bg-white p-8 text-center text-slate-500 shadow-sm">No enquiries yet.</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $inquiries->links() }}</div>
@endsection
