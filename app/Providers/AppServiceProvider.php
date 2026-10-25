<?php

namespace App\Providers;

use App\Support\Site\SiteContent;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Five failed sign-ins per email and IP per minute. Keyed by both so
        // one noisy IP cannot lock out a different account and vice versa.
        RateLimiter::for('admin-login', function (Request $request) {
            return Limit::perMinute(5)->by(
                Str::lower((string) $request->input('email')).'|'.$request->ip()
            );
        });

        Blade::directive('inr', function (string $expression) {
            return "<?php echo e(\\App\\Support\\Money::inr($expression)); ?>";
        });

        // Business details and translated service names for the public site.
        View::composer(['layouts.site', 'partials.site.*', 'site.*'], function ($view) {
            $view->with([
                'site' => SiteContent::company(),
                'siteServices' => SiteContent::services(),
                'siteCategories' => SiteContent::categories(),
            ]);
        });
    }
}
