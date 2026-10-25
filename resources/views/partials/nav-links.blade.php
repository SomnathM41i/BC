@php($dark = $dark ?? true)
@php($base = $dark ? 'hover:bg-slate-800' : 'text-slate-700 hover:bg-slate-100')
@php($active = $dark ? 'bg-slate-700 text-white' : 'bg-slate-100 font-medium text-slate-900')

<a href="{{ route('dashboard') }}"
   class="flex items-center gap-2 rounded-md px-3 py-2 {{ request()->routeIs('dashboard') ? $active : $base }}">
    <x-icon name="dashboard" />
    Dashboard
</a>
<a href="{{ route('projects.index') }}"
   class="flex items-center gap-2 rounded-md px-3 py-2 {{ request()->routeIs('projects.*') ? $active : $base }}">
    <x-icon name="projects" />
    Projects
</a>
<a href="{{ route('transactions.index') }}"
   class="flex items-center gap-2 rounded-md px-3 py-2 {{ request()->routeIs('transactions.*') ? $active : $base }}">
    <x-icon name="transactions" />
    Transactions
</a>
<a href="{{ route('owner.show') }}"
   class="flex items-center gap-2 rounded-md px-3 py-2 {{ request()->routeIs('owner.*') ? $active : $base }}">
    <x-icon name="wallet" />
    Personal Wallet
</a>
<a href="{{ route('contractors.index') }}"
   class="flex items-center gap-2 rounded-md px-3 py-2 {{ request()->routeIs('contractors.*') ? $active : $base }}">
    <x-icon name="users" />
    Contractors
</a>
