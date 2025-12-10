<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Contrôleur d'invitation à vérifier l'email
 */
class EmailVerificationPromptController extends Controller
{
    /**
     * Afficher l'invitation à vérifier l'email
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        return $request->user()->hasVerifiedEmail()
                    ? redirect()->intended(route('dashboard'))
                    : view('auth.verify-email');
    }
}