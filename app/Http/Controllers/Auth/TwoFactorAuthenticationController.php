<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

/**
 * Contrôleur d'authentification à deux facteurs (2FA)
 */
class TwoFactorAuthenticationController extends Controller
{
    /**
     * Afficher le challenge 2FA
     */
    public function create(): View
    {
        if (!session('auth.mfa_pending')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    /**
     * Vérifier le code 2FA
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ], [
            'code.required' => 'Le code de vérification est obligatoire.',
            'code.size' => 'Le code doit contenir 6 chiffres.',
        ]);

        $user = Auth::user();

        if (!$user || !$user->mfa_enabled) {
            return redirect()->route('login')->withErrors(['code' => 'Authentification invalide.']);
        }

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($user->mfa_secret, $request->code);

        if (!$valid) {
            // Log d'audit
            activity()
                ->causedBy($user)
                ->withProperties([
                    'ip' => $request->ip(),
                ])
                ->log('Échec de vérification 2FA');

            return back()->withErrors(['code' => 'Le code de vérification est incorrect.']);
        }

        // Authentification réussie
        $request->session()->forget('auth.mfa_pending');

        // Log d'audit
        activity()
            ->causedBy($user)
            ->withProperties([
                'ip' => $request->ip(),
            ])
            ->log('Vérification 2FA réussie');

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Activer l'authentification 2FA
     */
    public function enable(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->mfa_enabled) {
            return back()->withErrors(['mfa' => 'L\'authentification à deux facteurs est déjà activée.']);
        }

        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        $user->update([
            'mfa_secret' => $secret,
            'mfa_enabled' => true,
        ]);

        // Log d'audit
        activity()
            ->causedBy($user)
            ->log('Authentification 2FA activée');

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return back()->with([
            'status' => 'L\'authentification à deux facteurs a été activée.',
            'qr_code_url' => $qrCodeUrl,
            'secret' => $secret,
        ]);
    }

    /**
     * Désactiver l'authentification 2FA
     */
    public function disable(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (!$user->mfa_enabled) {
            return back()->withErrors(['mfa' => 'L\'authentification à deux facteurs n\'est pas activée.']);
        }

        $user->update([
            'mfa_secret' => null,
            'mfa_enabled' => false,
        ]);

        // Log d'audit
        activity()
            ->causedBy($user)
            ->log('Authentification 2FA désactivée');

        return back()->with('status', 'L\'authentification à deux facteurs a été désactivée.');
    }
}