<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistrationWizardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TwoFactorAuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register/step1', [RegistrationWizardController::class, 'step1']);
Route::post('/register/{unique_identifier}/step2', [RegistrationWizardController::class, 'step2']);
Route::post('/register/{unique_identifier}/step3', [RegistrationWizardController::class, 'step3']);
Route::post('/register/{unique_identifier}/step4', [RegistrationWizardController::class, 'step4']);
Route::post('/register/{unique_identifier}/step5', [RegistrationWizardController::class, 'step5']);
Route::get('/register/{unique_identifier}/resume', [RegistrationWizardController::class, 'resume']);
Route::get('/register/{unique_identifier}', [RegistrationWizardController::class, 'checkStatus']);

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle.login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::post('/2fa/generate', [TwoFactorAuthController::class, 'generateCode']);
Route::post('/2fa/verify', [TwoFactorAuthController::class, 'verifyCode']);
