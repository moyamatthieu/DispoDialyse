<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Contrôleur de renvoi de notification de vérification d'email
 */
class EmailVerificationNotificationController extends Controller
{
    /**
     * Renvoyer l'email de vérification
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard'));
        }

        $request->user()->sendEmailVerificationNotification();

        // Log d'audit
        activity()
            ->causedBy($request->user())
            ->log('Email de vérification renvoyé');

        return back()->with('status', 'Un nouvel email de vérification a été envoyé.');
    }
}