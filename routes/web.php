<?php

use App\Http\Controllers\Admin\InstallerController;
use App\Http\Controllers\Site\ContactController;
use App\Http\Controllers\Site\ContractorStatementController;
use App\Http\Controllers\Site\HomeController;
use App\Http\Controllers\Site\LegacyMarathiRedirectController;
use App\Http\Controllers\Site\PageController;
use App\Http\Controllers\Site\PortfolioController;
use App\Http\Controllers\Site\SitemapController;
use App\Http\Middleware\SetSiteLocale;
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

/*
|--------------------------------------------------------------------------
| Business website
|--------------------------------------------------------------------------
|
| Marathi lives at the root, English under /en. The URL is the source of
| truth for the language, so shared links and search results always open
| in the language they were written in.
|
*/
$siteRoutes = function () {
    Route::get('/', HomeController::class)->name('home');
    Route::get('about', [PageController::class, 'about'])->name('about');
    Route::get('services', [PageController::class, 'services'])->name('services');

    Route::get('projects', [PortfolioController::class, 'index'])->name('portfolio.index');
    Route::get('projects/{portfolio:slug}', [PortfolioController::class, 'show'])->name('portfolio.show');

    Route::get('contact', [ContactController::class, 'show'])->name('contact');
    Route::post('contact', [ContactController::class, 'store'])
        ->middleware('throttle:'.config('site.contact.throttle'))
        ->name('contact.store');
};

Route::middleware(SetSiteLocale::class.':mr')->group($siteRoutes);

Route::prefix('en')
    ->name('en.')
    ->middleware(SetSiteLocale::class.':en')
    ->group($siteRoutes);

// Keep old Marathi URLs working while making the root Marathi canonical.
Route::any('mr/{path?}', LegacyMarathiRedirectController::class)
    ->where('path', '.*');

Route::get('sitemap.xml', SitemapController::class)->name('sitemap');

/*
| Public contractor statement.
| No login required: the owner shares this link with the contractor.
|
| The URL carries a long random token, never the contractor's name, so it
| cannot be found by guessing. The page is noindex and no-store.
*/
Route::get('statement/{contractor:statement_token}', [ContractorStatementController::class, 'show'])
    ->name('statements.show');

// Retired guessable links (/contractors/ramesh-jadhav) answer 410 Gone.
Route::get('contractors/{slug}', [ContractorStatementController::class, 'legacy'])
    ->where('slug', '[A-Za-z0-9._-]+');

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
Route::redirect('transactions', '/admin/transactions');
Route::redirect('contractors', '/admin/contractors');
Route::redirect('owner', '/admin/owner');
Route::redirect('owner/{any}', '/admin/owner/{any}')->where('any', '.*');
