<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\PortfolioProject;
use App\Support\Site\SiteRoutes;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $locales = ['en', 'mr'];
        $pages = ['home', 'about', 'services', 'portfolio.index', 'contact'];

        $urls = [];

        foreach ($pages as $page) {
            foreach ($locales as $locale) {
                $urls[] = [
                    'loc' => SiteRoutes::urlForLocale($page, $locale),
                    'alternates' => [
                        'mr-IN' => SiteRoutes::urlForLocale($page, 'mr'),
                        'en-IN' => SiteRoutes::urlForLocale($page, 'en'),
                        'x-default' => SiteRoutes::urlForLocale($page, 'en'),
                    ],
                ];
            }
        }

        $projects = PortfolioProject::published()->ordered()->get();

        foreach ($projects as $project) {
            foreach ($locales as $locale) {
                $urls[] = [
                    'loc' => SiteRoutes::urlForLocale('portfolio.show', $locale, ['portfolio' => $project->slug]),
                    'alternates' => [
                        'mr-IN' => SiteRoutes::urlForLocale('portfolio.show', 'mr', ['portfolio' => $project->slug]),
                        'en-IN' => SiteRoutes::urlForLocale('portfolio.show', 'en', ['portfolio' => $project->slug]),
                        'x-default' => SiteRoutes::urlForLocale('portfolio.show', 'en', ['portfolio' => $project->slug]),
                    ],
                    'lastmod' => $project->updated_at,
                ];
            }
        }

        return response()
            ->view('site.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
