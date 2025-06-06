<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RegistrationWizard;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class RegistrationWizardController extends Controller
{
    // Step 1: Personal Information
    public function step1(Request $request)
    {
        try {
            $validated = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'gender' => 'required|in:male,female,other',
                'date_of_birth' => 'required|date',
                'email' => 'required|email|unique:registration_wizards,email',
                'nationality' => 'required|string|max:255',
                'phone_number' => 'required|string|max:20',
            ]);

            $wizard = RegistrationWizard::create([
                'unique_identifier' => Str::uuid(),
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'gender' => $validated['gender'],
                'date_of_birth' => $validated['date_of_birth'],
                'email' => $validated['email'],
                'nationality' => $validated['nationality'],
                'phone_number' => $validated['phone_number'],
                'current_step' => 1,
            ]);

            return response()->json([
                'message' => 'Step 1 completed successfully',
                'unique_identifier' => $wizard->unique_identifier,
                'current_step' => $wizard->current_step,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23505) { // PostgreSQL unique violation error code
                return response()->json([
                    'error' => 'This email address is already registered. Please use a different email address.',
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while processing your request.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Step 2: Address Details
    public function step2(Request $request, $unique_identifier)
    {
        $validated = $request->validate([
            'country_of_residence' => 'required|string',
            'city' => 'required|string',
            'postal_code' => 'required|string',
            'apartment_name' => 'nullable|string',
            'room_number' => 'nullable|string',
        ]);

        $wizard = RegistrationWizard::where('unique_identifier', $unique_identifier)->firstOrFail();
        if ($wizard->current_step < 1) {
            return response()->json(['error' => 'Complete previous steps first'], 400);
        }

        $is_expatriate = $wizard->nationality !== $validated['country_of_residence'];

        $wizard->update([
            'country_of_residence' => $validated['country_of_residence'],
            'city' => $validated['city'],
            'postal_code' => $validated['postal_code'],
            'apartment_name' => $validated['apartment_name'],
            'room_number' => $validated['room_number'],
            'is_expatriate' => $is_expatriate,
            'current_step' => 2,
        ]);

        return response()->json([
            'message' => 'Step 2 completed',
            'unique_identifier' => $wizard->unique_identifier,
            'current_step' => $wizard->current_step,
        ]);
    }

    // Step 3: Two-Factor Authentication (2FA)
    public function step3(Request $request, $unique_identifier)
    {
        $wizard = RegistrationWizard::where('unique_identifier', $unique_identifier)->firstOrFail();
        if ($wizard->current_step < 2) {
            return response()->json(['error' => 'Complete previous steps first'], 400);
        }

        // Here you would send a 2FA code to the user's email and verify it
        // For demo, we just mark as verified
        $wizard->update([
            'two_factor_verified' => true,
            'current_step' => 3,
        ]);

        return response()->json([
            'message' => 'Step 3 (2FA) completed',
            'unique_identifier' => $wizard->unique_identifier,
            'current_step' => $wizard->current_step,
        ]);
    }

    // Step 4: Password Setup
    public function step4(Request $request, $unique_identifier)
    {
        try {
            $wizard = RegistrationWizard::where('unique_identifier', $unique_identifier)->first();

            if (!$wizard) {
                return response()->json([
                    'error' => 'Registration session not found. Please start over.',
                ], 404);
            }

            // Check if previous steps are completed
            if ($wizard->current_step < 3) {
                return response()->json([
                    'error' => 'Please complete steps 2 and 3 before setting your password.',
                    'current_step' => $wizard->current_step,
                    'required_step' => 3,
                    'steps_completed' => [
                        'step1' => $wizard->current_step >= 1,
                        'step2' => $wizard->current_step >= 2,
                        'step3' => $wizard->current_step >= 3
                    ]
                ], 422);
            }

            $request->validate([
                'password' => 'required|min:8|confirmed',
            ]);

            $wizard->update([
                'password' => bcrypt($request->input('password')),
                'current_step' => 4,
            ]);

            return response()->json([
                'message' => 'Password updated successfully',
                'current_step' => $wizard->current_step,
                'unique_identifier' => $wizard->unique_identifier
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while updating your password.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Step 5: Review & Confirm
    public function step5(Request $request, $unique_identifier)
    {
        $wizard = RegistrationWizard::where('unique_identifier', $unique_identifier)->firstOrFail();
        if ($wizard->current_step < 4) {
            return response()->json(['error' => 'Complete previous steps first'], 400);
        }

        // Here you would show all data for review and, on confirmation, move to final user creation
        // For demo, just mark as completed
        $wizard->update([
            'current_step' => 5,
        ]);

        return response()->json([
            'message' => 'Step 5 (Review & Confirm) completed',
            'unique_identifier' => $wizard->unique_identifier,
            'current_step' => $wizard->current_step,
        ]);
    }

    // Resume registration by unique identifier
    public function resume($unique_identifier)
    {
        $wizard = RegistrationWizard::where('unique_identifier', $unique_identifier)->firstOrFail();
        return response()->json($wizard);
    }

    public function checkStatus($unique_identifier)
    {
        $registration = RegistrationWizard::where('unique_identifier', $unique_identifier)->first();

        if (!$registration) {
            return response()->json([
                'error' => 'Registration not found'
            ], 404);
        }

        return response()->json([
            'current_step' => $registration->current_step,
            'steps_completed' => [
                'step1' => !empty($registration->first_name) && !empty($registration->last_name),
                'step2' => !empty($registration->address_line1) && !empty($registration->city),
                'step3' => $registration->two_factor_verified,
                'step4' => !empty($registration->password),
                'step5' => $registration->terms_accepted
            ],
            'registration_data' => [
                'first_name' => $registration->first_name,
                'last_name' => $registration->last_name,
                'email' => $registration->email,
                'gender' => $registration->gender,
                'date_of_birth' => $registration->date_of_birth,
                'nationality' => $registration->nationality,
                'phone_number' => $registration->phone_number,
                'address_line1' => $registration->address_line1,
                'city' => $registration->city,
                'state' => $registration->state,
                'postal_code' => $registration->postal_code,
                'country' => $registration->country,
                'two_factor_verified' => $registration->two_factor_verified,
                'terms_accepted' => $registration->terms_accepted
            ]
        ]);
    }
}
