<?php

namespace App\Http\Controllers\Auth;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

/**
 * Contrôleur d'enregistrement des utilisateurs
 * 
 * Réservé aux administrateurs pour créer de nouveaux comptes
 */
class RegisteredUserController extends Controller
{
    /**
     * Afficher le formulaire d'inscription
     */
    public function create(): View
    {
        // Vérifier que l'utilisateur est admin
        abort_unless(auth()->user()->isAdmin(), 403, 'Accès réservé aux administrateurs');

        return view('auth.register', [
            'roles' => RoleEnum::options(),
        ]);
    }

    /**
     * Traiter l'enregistrement d'un nouvel utilisateur
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Vérifier que l'utilisateur est admin
        abort_unless(auth()->user()->isAdmin(), 403, 'Accès réservé aux administrateurs');

        $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:'.User::class],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'string', 'in:'.implode(',', array_column(RoleEnum::cases(), 'value'))],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'username.required' => 'Le nom d\'utilisateur est obligatoire.',
            'username.unique' => 'Ce nom d\'utilisateur est déjà utilisé.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email n\'est pas valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom est obligatoire.',
            'role.required' => 'Le rôle est obligatoire.',
            'role.in' => 'Le rôle sélectionné n\'est pas valide.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'role' => $request->role,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        event(new Registered($user));

        // Log d'audit
        activity()
            ->causedBy(auth()->user())
            ->performedOn($user)
            ->withProperties([
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role->value,
            ])
            ->log('Nouvel utilisateur créé');

        return redirect()->route('dashboard')->with('success', 'Utilisateur créé avec succès.');
    }
}