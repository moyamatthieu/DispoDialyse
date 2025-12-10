<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Contrôleur de gestion des sessions authentifiées
 * 
 * Gère la connexion et la déconnexion des utilisateurs
 */
class AuthenticatedSessionController extends Controller
{
    /**
     * Afficher la page de connexion
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Traiter la tentative de connexion
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Enregistrer la connexion dans les logs
        $user = Auth::user();
        $user->recordLogin($request->ip());

        // Log d'audit
        activity()
            ->causedBy($user)
            ->withProperties([
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ])
            ->log('Connexion réussie');

        // Vérifier si 2FA est activé
        if ($user->mfa_enabled) {
            $request->session()->put('auth.mfa_pending', true);
            return redirect()->route('two-factor.challenge');
        }

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Déconnecter l'utilisateur
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Log d'audit avant déconnexion
        if (Auth::check()) {
            activity()
                ->causedBy(Auth::user())
                ->withProperties([
                    'ip' => $request->ip(),
                ])
                ->log('Déconnexion');
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}