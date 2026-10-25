<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\UserRole;
use App\Http\Requests\Admin\StoreManagerRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ManagerController extends Controller
{
    public function index(): View
    {
        $managers = User::query()
            ->managers()
            ->withCount('managedProjects')
            ->orderBy('name')
            ->get();

        return view('admin.managers.index', [
            'managers' => $managers,
        ]);
    }

    public function store(StoreManagerRequest $request): RedirectResponse
    {
        $name = $request->validated('name');
        $email = $request->validated('email');
        $password = $request->validated('password');

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => UserRole::Manager,
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.managers.index')
            ->with('status', 'Manager created successfully.')
            // Flashed for one request only so the password can be handed over once.
            ->with('new_manager_credentials', [
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ]);
    }
}
