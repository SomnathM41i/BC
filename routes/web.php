<?php

use App\Http\Controllers\ContractorController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstallerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public surface
|--------------------------------------------------------------------------
|
| The business website, the public contractor statement and the one-time
| browser installer live here. Everything internal is mounted under /admin
| from routes/admin.php (see bootstrap/app.php).
|
*/

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

Route::get('/', HomeController::class)->name('home');

/*
| Public contractor statement.
| No login required: the owner can share this link with the contractor.
| The URL starts with the contractor's name, e.g. /contractors/ramesh-jadhav.
|
| NOTE: temporary location. This becomes an unguessable /statement/{token}
| link in phase 5 of the restructure plan.
*/
Route::get('contractors/{contractor}', [ContractorController::class, 'show'])
    ->name('contractors.show');

/*
|--------------------------------------------------------------------------
| Legacy internal URLs
|--------------------------------------------------------------------------
|
| The internal app moved from / to /admin. These redirects keep old
| bookmarks working for one release. Remove them once the move has settled.
|
*/
Route::redirect('login', '/admin/login');
Route::redirect('dashboard', '/admin/dashboard');
Route::redirect('projects', '/admin/projects');
Route::redirect('projects/{any}', '/admin/projects/{any}')->where('any', '.*');
Route::redirect('transactions', '/admin/transactions');
Route::redirect('contractors', '/admin/contractors');
Route::redirect('owner', '/admin/owner');
Route::redirect('owner/{any}', '/admin/owner/{any}')->where('any', '.*');
