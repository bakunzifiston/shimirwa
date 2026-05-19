<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\ContactRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('pages.contact');
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        // Extend with Mail::to() when mail is configured.
        return redirect()
            ->route('contact')
            ->with('success', 'Thank you for your message. We will get back to you soon.');
    }
}
