<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\PortfolioProject;
use App\Support\Site\SiteContent;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('site.home', [
            'projects' => PortfolioProject::published()->ordered()->limit(3)->get(),
            'services' => array_slice(SiteContent::services(), 0, 6),
        ]);
    }
}
