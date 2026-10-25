<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Contractor;
use App\Services\WalletService;
use Illuminate\Http\Response;

class ContractorStatementController extends Controller
{
    public function __construct(private readonly WalletService $wallets) {}

    /**
     * Public, login-free statement the owner can share with a contractor.
     *
     * The URL carries a long random token, not the contractor's slug, so a
     * statement cannot be found by guessing names. Search engines are kept
     * out with both a robots header and a no-store cache policy.
     */
    public function show(Contractor $contractor): Response
    {
        $contractor->load('type');

        return $this->privateResponse('site.statement', [
            'contractor' => $contractor,
            'report' => $this->wallets->contractorReport($contractor),
        ]);
    }

    /**
     * Old /contractors/{slug} links. They exposed statements through a
     * guessable URL, so they are retired with 410 Gone rather than redirected.
     */
    public function legacy(): Response
    {
        return $this->privateResponse('site.statement-unavailable', [], 410);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function privateResponse(string $view, array $data, int $status = 200): Response
    {
        return response()
            ->view($view, $data, $status)
            ->header('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private');
    }
}
