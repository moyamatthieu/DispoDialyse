<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Requête de validation pour la connexion
 */
class LoginRequest extends FormRequest
{
    /**
     * Déterminer si l'utilisateur est autorisé à faire cette requête
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Messages de validation personnalisés
     */
    public function messages(): array
    {
        return [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'password.required' => 'Le mot de passe est obligatoire.',
        ];
    }

    /**
     * Tenter d'authentifier l'utilisateur
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // Tentative de connexion avec email
        if (! Auth::attempt([
            'email' => $this->email,
            'password' => $this->password,
            'is_active' => true,
        ], $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            // Log de tentative échouée
            activity()
                ->withProperties([
                    'email' => $this->email,
                    'ip' => $this->ip(),
                    'user_agent' => $this->userAgent(),
                ])
                ->log('Tentative de connexion échouée');

            throw ValidationException::withMessages([
                'email' => 'Les identifiants fournis sont incorrects ou le compte est inactif.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * S'assurer que la requête n'est pas limitée par le rate limiting
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Obtenir la clé de throttling pour la requête
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}