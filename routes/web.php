<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContractorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstallerController;
use App\Http\Controllers\OwnerWalletController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectFinanceController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Deployment / installer routes
|--------------------------------------------------------------------------
|
| These routes let you run migrations and seeders from the browser on shared
| hosting where there is no terminal or SSH access. They are disabled unless
| a DEPLOY_TOKEN is set in the .env file. Remove DEPLOY_TOKEN after installing
| to turn the installer off completely.
|
*/
Route::prefix('deploy')->group(function () {
    Route::get('/', [InstallerController::class, 'index'])->name('deploy.index');
    Route::post('/run', [InstallerController::class, 'run'])->name('deploy.run');
});

Route::get('/', HomeController::class);

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});

Route::post('logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
| Public contractor statement.
| No login required: the owner can share this link with the contractor.
| The URL starts with the contractor's name, e.g. /contractors/ramesh-jadhav.
*/
Route::get('contractors/{contractor}', [ContractorController::class, 'show'])
    ->name('contractors.show');

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
