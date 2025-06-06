<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\RegistrationWizard;

class AuthController extends Controller
{
    // Login
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            // First check if the registration is complete
            $wizard = RegistrationWizard::where('email', $request->email)->first();

            if (!$wizard) {
                return response()->json([
                    'error' => 'No registration found for this email. Please register first.'
                ], 404);
            }

            if ($wizard->current_step < 5) {
                return response()->json([
                    'error' => 'Registration is not complete. Please complete all steps first.',
                    'current_step' => $wizard->current_step,
                    'required_step' => 5
                ], 422);
            }

            // Verify password
            if (!Hash::check($request->password, $wizard->password)) {
                return response()->json([
                    'error' => 'Invalid credentials'
                ], 401);
            }

            // Create token
            $token = $wizard->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'email' => $wizard->email,
                    'name' => $wizard->first_name . ' ' . $wizard->last_name,
                    'unique_identifier' => $wizard->unique_identifier
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred during login',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }
}
