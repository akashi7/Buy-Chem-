<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistrationWizardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TwoFactorAuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('/register/step1', [RegistrationWizardController::class, 'step1']);
Route::post('/register/{unique_identifier}/step2', [RegistrationWizardController::class, 'step2']);
Route::post('/register/{unique_identifier}/step3', [RegistrationWizardController::class, 'step3']);
Route::post('/register/{unique_identifier}/step4', [RegistrationWizardController::class, 'step4']);
Route::post('/register/{unique_identifier}/step5', [RegistrationWizardController::class, 'step5']);
Route::get('/register/{unique_identifier}/resume', [RegistrationWizardController::class, 'resume']);

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle.login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::post('/2fa/generate', [TwoFactorAuthController::class, 'generateCode']);
Route::post('/2fa/verify', [TwoFactorAuthController::class, 'verifyCode']);
