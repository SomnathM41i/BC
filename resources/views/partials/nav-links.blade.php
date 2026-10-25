@php($dark = $dark ?? true)
@php($base = $dark ? 'hover:bg-slate-800' : 'text-slate-700 hover:bg-slate-100')
@php($active = $dark ? 'bg-slate-700 text-white' : 'bg-slate-100 font-medium text-slate-900')

<a href="{{ route('admin.dashboard') }}"
   class="flex items-center gap-2 rounded-md px-3 py-2 {{ request()->routeIs('admin.dashboard') ? $active : $base }}">
    <x-icon name="dashboard" />
    Dashboard
</a>
<a href="{{ route('admin.projects.index') }}"
   class="flex items-center gap-2 rounded-md px-3 py-2 {{ request()->routeIs('admin.projects.*') ? $active : $base }}">
    <x-icon name="projects" />
    Projects
</a>
<a href="{{ route('admin.transactions.index') }}"
   class="flex items-center gap-2 rounded-md px-3 py-2 {{ request()->routeIs('admin.transactions.*') ? $active : $base }}">
    <x-icon name="transactions" />
    Transactions
</a>
<a href="{{ route('admin.owner.show') }}"
   class="flex items-center gap-2 rounded-md px-3 py-2 {{ request()->routeIs('admin.owner.*') ? $active : $base }}">
    <x-icon name="wallet" />
    Personal Wallet
</a>
<a href="{{ route('admin.contractors.index') }}"
   class="flex items-center gap-2 rounded-md px-3 py-2 {{ request()->routeIs('admin.contractors.*') ? $active : $base }}">
    <x-icon name="users" />
    Contractors
</a>
