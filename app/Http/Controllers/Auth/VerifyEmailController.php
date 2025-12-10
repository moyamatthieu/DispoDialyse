<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

/**
 * Contrôleur de vérification d'email
 */
class VerifyEmailController extends Controller
{
    /**
     * Marquer l'email comme vérifié
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard').'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));

            // Log d'audit
            activity()
                ->causedBy($request->user())
                ->log('Email vérifié');
        }

        return redirect()->intended(route('dashboard').'?verified=1');
    }
}