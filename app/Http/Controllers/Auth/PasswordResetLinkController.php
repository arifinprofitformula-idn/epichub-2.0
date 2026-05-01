<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;

class PasswordResetLinkController
{
    /**
     * Display the password reset link request view.
     */
    public function create(): Response
    {
        return response()->view('pages.auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     * Custom implementation to redirect to success page after email is sent.
     */
    public function store(Request $request): Response|\Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Always redirect to success page regardless of status
        // This prevents enumeration attacks (user can't tell if email exists)
        return redirect()->route('password.reset.sent');
    }
}
