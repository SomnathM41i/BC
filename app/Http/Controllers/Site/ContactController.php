<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\StoreInquiryRequest;
use App\Mail\InquiryReceived;
use App\Models\Inquiry;
use App\Support\Site\SiteContent;
use App\Support\Site\SiteRoutes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('site.contact', [
            'services' => SiteContent::services(),
        ]);
    }

    public function store(StoreInquiryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Honeypot: bots fill the hidden field, humans never see it.
        if (filled($data['company_website'] ?? null)) {
            return $this->confirmation();
        }

        unset($data['company_website']);

        $inquiry = Inquiry::create([
            ...$data,
            'locale' => app()->getLocale(),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 250, ''),
        ]);

        $this->notify($inquiry);

        return $this->confirmation();
    }

    private function confirmation(): RedirectResponse
    {
        return redirect()
            ->to(SiteRoutes::url('contact').'#enquiry-form')
            ->with('inquiry_sent', true);
    }

    private function notify(Inquiry $inquiry): void
    {
        $recipient = config('site.contact.notify_email') ?: config('site.email');

        if (blank($recipient)) {
            return;
        }

        try {
            Mail::to($recipient)->send(new InquiryReceived($inquiry));
        } catch (Throwable $exception) {
            Log::warning('Enquiry notification email failed.', [
                'inquiry_id' => $inquiry->getKey(),
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
