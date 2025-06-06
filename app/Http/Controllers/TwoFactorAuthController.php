<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\RegistrationWizard;

class TwoFactorAuthController extends Controller
{
    public function generateCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $code = Str::random(6);
        // In a real app, store this code in cache or database with an expiry
        // For demo, we'll just send it via email
        Mail::html("
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; }
                    .container { padding: 20px; }
                    .code { font-size: 24px; font-weight: bold; color: #3B82F6; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>Your 2FA Code</h2>
                    <p>Your 2FA code is: <span class='code'>{$code}</span></p>
                </div>
            </body>
            </html>
        ", function ($message) use ($request) {
            $message->to($request->email)
                    ->subject('Your 2FA Code');
        });

        return response()->json(['message' => '2FA code sent successfully']);
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        try {
            // Find the registration wizard by email only
            $wizard = RegistrationWizard::where('email', $request->email)->first();

            if (!$wizard) {
                return response()->json([
                    'error' => 'No registration found for this email address. Please start the registration process again.'
                ], 404);
            }

            // Check if the current step is valid for 2FA
            if ($wizard->current_step < 2) {
                return response()->json([
                    'error' => 'Please complete step 2 (Address Information) before verifying 2FA.',
                    'current_step' => $wizard->current_step
                ], 422);
            }

            // Update the current step to 3
            $wizard->update(['current_step' => 3]);

            return response()->json([
                'message' => '2FA code verified successfully',
                'current_step' => $wizard->current_step,
                'unique_identifier' => $wizard->unique_identifier
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while verifying the code.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
