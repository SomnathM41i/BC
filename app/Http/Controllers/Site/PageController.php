<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Support\Site\SiteContent;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('site.about', [
            'services' => array_slice(SiteContent::services(), 0, 6),
        ]);
    }

    public function services(): View
    {
        return view('site.services', [
            'services' => SiteContent::services(),
        ]);
    }
}
