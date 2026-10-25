@extends('layouts.admin')

@section('title', 'Managers')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">Managers</h1>
        <p class="text-sm text-slate-500">Add project managers and assign them to projects.</p>
    </div>

    @if (session('new_manager_credentials'))
        @php($credentials = session('new_manager_credentials'))
        <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-5">
            <h2 class="font-semibold text-amber-900">Save these credentials now</h2>
            <p class="mb-3 text-sm text-amber-800">
                This password is shown only once and is stored securely (hashed) afterwards. Copy it and share it with the manager.
            </p>
            <dl class="grid grid-cols-1 gap-2 text-sm sm:grid-cols-3">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-amber-700">Name</dt>
                    <dd class="font-medium text-amber-900">{{ $credentials['name'] }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-amber-700">Email</dt>
                    <dd class="font-medium text-amber-900">{{ $credentials['email'] }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-amber-700">Password</dt>
                    <dd class="font-mono font-medium text-amber-900">{{ $credentials['password'] }}</dd>
                </div>
            </dl>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="font-semibold text-slate-900">All managers</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Email</th>
                                <th class="px-4 py-3">Projects</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($managers as $manager)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 font-medium text-slate-900">{{ $manager->name }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $manager->email }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $manager->managed_projects_count }}</td>
                                    <td class="px-4 py-3">
                                        @if ($manager->is_active)
                                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">Active</span>
                                        @else
                                            <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs font-medium text-slate-700">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-slate-500">No managers yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div>
            <form method="POST" action="{{ route('admin.managers.store') }}" class="space-y-3 rounded-xl bg-white p-5 shadow-sm">
                @csrf
                <h2 class="font-semibold text-slate-900">Add a manager</h2>
                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" required
                           class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                </div>
                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required
                           class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                </div>
                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                    <input id="password" name="password" type="password" required
                           class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                    <p class="mt-1 text-xs text-slate-500">Minimum 8 characters.</p>
                </div>
                <div>
                    <label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-700">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                           class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none">
                </div>
                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    <x-icon name="users" />
                    Create manager
                </button>
            </form>
        </div>
    </div>
@endsection
