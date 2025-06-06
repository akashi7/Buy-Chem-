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
        Mail::send([], [], function ($message) use ($request, $code) {
            $message->to($request->email)
                ->subject('Your Buy Chem Japan Security Code')
                ->html('
                    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8f9fa;">
                        <div style="background-color: #ffffff; border-radius: 8px; padding: 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <h1 style="color: #2c3e50; text-align: center; margin-bottom: 20px;">Security Verification Code</h1>
                            
                            <p style="color: #666; text-align: center; margin-bottom: 25px;">
                                To complete your registration, please use the following security code:
                            </p>
                            
                            <div style="background-color: #f8f9fa; border: 2px solid #e9ecef; border-radius: 6px; padding: 20px; margin: 20px 0; text-align: center;">
                                <span style="font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #2c3e50;">' . $code . '</span>
                            </div>
                            
                            <p style="color: #666; text-align: center; margin-bottom: 15px; font-size: 14px;">
                                This code will expire in 10 minutes.
                            </p>
                            
                            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef;">
                                <p style="color: #666; text-align: center; font-size: 12px; margin: 0;">
                                    If you did not request this code, please ignore this email or contact support if you have concerns.
                                </p>
                            </div>
                        </div>
                        
                        <div style="text-align: center; margin-top: 20px; padding: 20px; color: #666; font-size: 12px;">
                            <p style="margin: 0;">
                                © ' . date('Y') . ' Buy Chem Japan. All rights reserved.
                            </p>
                        </div>
                    </div>
                ');
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
