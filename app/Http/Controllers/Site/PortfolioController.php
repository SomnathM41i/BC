<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\PortfolioProject;
use App\Support\Site\SiteContent;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortfolioController extends Controller
{
    public function index(Request $request): View
    {
        $categories = SiteContent::categories();
        $category = (string) $request->query('category', '');

        if (! in_array($category, array_column($categories, 'key'), true)) {
            $category = '';
        }

        $projects = PortfolioProject::published()
            ->ordered()
            ->when($category !== '', fn ($query) => $query->where('category', $category))
            ->get();

        return view('site.portfolio.index', [
            'projects' => $projects,
            'categories' => $categories,
            'category' => $category,
        ]);
    }

    public function show(PortfolioProject $portfolio): View
    {
        abort_unless($portfolio->is_published, 404);

        $related = PortfolioProject::published()
            ->whereKeyNot($portfolio->getKey())
            ->orderByRaw('category = ? desc', [$portfolio->category])
            ->ordered()
            ->limit(3)
            ->get();

        return view('site.portfolio.show', [
            'project' => $portfolio,
            'related' => $related,
        ]);
    }
}
