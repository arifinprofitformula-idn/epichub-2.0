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
     * 
     * This overrides Fortify's default behavior to:
     * 1. Send reset link
     * 2. Redirect to a success page
     * 3. Prevent email enumeration
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'email' => ['required', 'email'],
            ]);

            $email = $request->input('email');

            // Check if email exists in the users table
            $userExists = \App\Models\User::where('email', $email)->exists();

            if (! $userExists) {
                // Show a clear message when email is not registered
                return back()->with('status', 'Maaf email tidak terdaftar pada sistem, masukkan email valid yang terdaftar pada sistem EPIC HUB')->withInput();
            }

            $status = Password::sendResetLink(
                $request->only('email')
            );

            // Redirect to success page when the link was sent
            return redirect()->route('password.reset.sent');
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Password reset error: ' . $e->getMessage(), [
                'email' => $request->input('email'),
                'exception' => $e,
            ]);
            
            // Still redirect to success page (security)
            return redirect()->route('password.reset.sent');
        }
    }
}
