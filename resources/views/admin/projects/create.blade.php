@extends('layouts.admin')

@section('title', 'New Project')

@section('content')
    <div class="mx-auto max-w-xl">
        <h1 class="mb-1 text-2xl font-semibold text-slate-900">New Project</h1>
        <p class="mb-6 text-sm text-slate-500">
            Enter the contract value agreed with the client. Receipts are recorded separately as payments are received.
        </p>

        <form method="POST" action="{{ route('admin.projects.store') }}" class="space-y-4 rounded-xl bg-white p-6 shadow-sm">
            @csrf
            <div>
                <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Project Name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
            </div>
            <div>
                <label for="location" class="mb-1 block text-sm font-medium text-slate-700">Location</label>
                <input id="location" name="location" type="text" value="{{ old('location') }}"
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
            </div>

            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                <h2 class="text-sm font-semibold text-slate-900">Client Details</h2>
                <p class="mt-1 text-xs text-slate-500">
                    These are used by the public Track Your Payments portal. Enter the mobile number the client will use to look up their project.
                </p>

                <div class="mt-3 space-y-3">
                    <div>
                        <label for="client_name" class="mb-1 block text-sm font-medium text-slate-700">Client Full Name</label>
                        <input id="client_name" name="client_name" type="text" value="{{ old('client_name') }}" required
                               class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                        @error('client_name')
                            <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="client_mobile" class="mb-1 block text-sm font-medium text-slate-700">Client Mobile Number</label>
                        <input id="client_mobile" name="client_mobile" type="tel" value="{{ old('client_mobile') }}" required
                               inputmode="tel" placeholder="9876543210"
                               class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                        @error('client_mobile')
                            <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div>
                <label for="agreed_amount" class="mb-1 block text-sm font-medium text-slate-700">Contract Value (₹)</label>
                <input id="agreed_amount" name="agreed_amount" type="number" step="0.01" min="0"
                       value="{{ old('agreed_amount') }}" required
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
            </div>
            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    <x-icon name="plus" />
                    Create Project
                </button>
                <a href="{{ route('admin.projects.index') }}" class="text-sm text-slate-600 underline">Cancel</a>
            </div>
        </form>
    </div>
@endsection
