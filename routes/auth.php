<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\TwoFactorAuthenticationController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes d'Authentification
|--------------------------------------------------------------------------
|
| Routes générées pour Laravel Breeze avec support 2FA
|
*/

Route::middleware('guest')->group(function () {
    // Inscription (réservée aux admins)
    Route::get('register', [RegisteredUserController::class, 'create'])
                ->name('register')
                ->middleware('auth'); // L'admin doit être connecté
    
    Route::post('register', [RegisteredUserController::class, 'store'])
                ->middleware('auth');

    // Connexion
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
                ->name('login');
    
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // Mot de passe oublié
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
                ->name('password.request');
    
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
                ->name('password.email');

    // Réinitialisation du mot de passe
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
                ->name('password.reset');
    
    Route::post('reset-password', [NewPasswordController::class, 'store'])
                ->name('password.store');
});

Route::middleware('auth')->group(function () {
    // Vérification d'email
    Route::get('verify-email', EmailVerificationPromptController::class)
                ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
                ->middleware(['signed', 'throttle:6,1'])
                ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                ->middleware('throttle:6,1')
                ->name('verification.send');

    // Authentification à deux facteurs (2FA)
    Route::get('two-factor-challenge', [TwoFactorAuthenticationController::class, 'create'])
                ->name('two-factor.challenge');
    
    Route::post('two-factor-challenge', [TwoFactorAuthenticationController::class, 'store']);

    Route::post('two-factor/enable', [TwoFactorAuthenticationController::class, 'enable'])
                ->name('two-factor.enable');
    
    Route::post('two-factor/disable', [TwoFactorAuthenticationController::class, 'disable'])
                ->name('two-factor.disable');

    // Déconnexion
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
                ->name('logout');
});