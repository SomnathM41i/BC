<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public function index(): View
    {
        return view('admin.inquiries.index', [
            'inquiries' => Inquiry::query()->latest()->latest('id')->paginate(20),
        ]);
    }

    public function updateStatus(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:'.implode(',', [Inquiry::STATUS_NEW, Inquiry::STATUS_READ, Inquiry::STATUS_CLOSED])],
        ]);

        $inquiry->update(['status' => $validated['status']]);

        return redirect()
            ->back()
            ->with('status', 'Enquiry updated.');
    }
}
