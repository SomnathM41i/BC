<?php

namespace App\Http\Middleware;

use App\Support\Site\SiteRoutes;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate for the verified side of the public payment portal.
 *
 * The session stores only the identity chosen after a successful lookup. No
 * URL parameter is trusted, and the verification expires so a shared device
 * cannot keep showing someone else's payments indefinitely.
 */
class EnsurePaymentTrackingVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $data = $request->session()->get('payment_tracking');
        $category = is_array($data) ? ($data['category'] ?? null) : null;

        $expired = ! is_array($data)
            || empty($data['verified_at'])
            || (now()->timestamp - (int) $data['verified_at']) > ((int) config('site.payment_tracking.session_minutes', 30) * 60);

        $missingIdentity = match ($category) {
            'client' => empty($data['project_ids']) || ! is_array($data['project_ids']),
            'contractor' => empty($data['contractor_id']),
            'employee' => empty($data['employee_id']),
            default => true,
        };

        if ($expired || $missingIdentity) {
            $request->session()->forget('payment_tracking');

            return redirect()
                ->to(SiteRoutes::url('payment-tracking.index'))
                ->with('tracking_error', __('site.payment_tracking.session_expired'));
        }

        return $next($request);
    }
}
