<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LegacyMarathiRedirectController extends Controller
{
    public function __invoke(Request $request, ?string $path = null): RedirectResponse
    {
        $target = '/'.ltrim((string) $path, '/');
        $status = $request->isMethodSafe() ? 301 : 307;

        if ($request->getQueryString()) {
            $target .= '?'.$request->getQueryString();
        }

        return redirect($target, $status);
    }
}
