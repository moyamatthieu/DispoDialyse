<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

/**
 * Contrôleur de demande de réinitialisation de mot de passe
 */
class PasswordResetLinkController extends Controller
{
    /**
     * Afficher le formulaire de demande de réinitialisation
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Traiter la demande de lien de réinitialisation
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email n\'est pas valide.',
        ]);

        // Envoyer le lien de réinitialisation
        $status = Password::sendResetLink(
            $request->only('email')
        );

        // Log d'audit
        activity()
            ->withProperties([
                'email' => $request->email,
                'ip' => $request->ip(),
                'status' => $status,
            ])
            ->log('Demande de réinitialisation de mot de passe');

        return $status === Password::RESET_LINK_SENT
                    ? back()->with('status', 'Un lien de réinitialisation a été envoyé à votre adresse email.')
                    : back()->withInput($request->only('email'))
                            ->withErrors(['email' => 'Nous ne trouvons pas d\'utilisateur avec cette adresse email.']);
    }
}