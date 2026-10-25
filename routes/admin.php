<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ContractorController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OwnerWalletController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ProjectFinanceController;
use App\Http\Controllers\Admin\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin surface
|--------------------------------------------------------------------------
|
| Mounted at /admin with the "admin." route-name prefix from bootstrap/app.php.
| Everything internal (wallet, projects, owner wallet, contractors) lives here.
| The public website and the contractor statement stay in routes/web.php.
|
*/

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:admin-login')
        ->name('login.attempt');
});

Route::post('logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role:owner'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('projects/{project}', [ProjectController::class, 'show'])
        ->whereNumber('project')->name('projects.show');

    Route::get('projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');

    Route::post('projects/{project}/receipts', [ProjectFinanceController::class, 'storeReceipt'])
        ->whereNumber('project')->name('projects.receipts.store');
    Route::post('projects/{project}/expenses', [ProjectFinanceController::class, 'storeExpense'])
        ->whereNumber('project')->name('projects.expenses.store');
    Route::post('projects/{project}/transfers', [ProjectFinanceController::class, 'storeTransfer'])
        ->whereNumber('project')->name('projects.transfers.store');
    Route::post('projects/{project}/fund', [ProjectFinanceController::class, 'storeFund'])
        ->whereNumber('project')->name('projects.fund');

    Route::post('projects/{project}/request-closure', [ProjectController::class, 'requestClosure'])
        ->whereNumber('project')->name('projects.request-closure');
    Route::post('projects/{project}/finalize-closure', [ProjectController::class, 'finalizeClosure'])
        ->whereNumber('project')->name('projects.finalize-closure');

    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');

    Route::get('contractors', [ContractorController::class, 'index'])->name('contractors.index');
    Route::post('contractors', [ContractorController::class, 'store'])->name('contractors.store');

    Route::get('owner', [OwnerWalletController::class, 'show'])->name('owner.show');
    Route::post('owner/income', [OwnerWalletController::class, 'income'])->name('owner.income');
    Route::post('owner/transfer', [OwnerWalletController::class, 'transfer'])->name('owner.transfer');
    Route::post('owner/return', [OwnerWalletController::class, 'returnFunds'])->name('owner.return');
    Route::post('owner/expense', [OwnerWalletController::class, 'expense'])->name('owner.expense');
});
