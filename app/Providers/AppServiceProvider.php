<?php

namespace App\Providers;

use App\Support\Site\SiteContent;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
