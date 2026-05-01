<?php

namespace App\Actions\Fortify;

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;

class SendPasswordResetLink
{
    /**
     * Send password reset link to the given email address.
     * 
     * This action is registered with Fortify to handle password reset requests.
     * It ensures proper error handling and logging in production.
     *
     * @param  string  $email
     * @return string
     */
    public function __invoke($email)
    {
        try {
            $status = Password::sendResetLink(['email' => $email]);
            
            // Log success for monitoring
            Log::info('Password reset link sent', ['email' => $email, 'status' => $status]);
            
            return $status;
        } catch (\Exception $e) {
            Log::error('Failed to send password reset link', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Return a status constant to indicate failure
            throw $e;
        }
    }
}
