<?php

namespace App\Http\Middleware;

use App\Enums\RoleEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de vérification des rôles utilisateur
 * 
 * Vérifie si l'utilisateur authentifié possède l'un des rôles autorisés
 */
class CheckRole
{
    /**
     * Gérer une requête entrante
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles Liste des rôles autorisés (valeurs de l'enum)
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            abort(401, 'Non authentifié');
        }

        $user = $request->user();
        $userRole = $user->role->value;

        // Vérifier si le rôle de l'utilisateur est dans la liste des rôles autorisés
        if (!in_array($userRole, $roles)) {
            // Log d'audit pour tentative d'accès non autorisé
            activity()
                ->causedBy($user)
                ->withProperties([
                    'url' => $request->url(),
                    'required_roles' => $roles,
                    'user_role' => $userRole,
                    'ip' => $request->ip(),
                ])
                ->log('Tentative d\'accès non autorisé');

            abort(403, 'Accès refusé. Vous n\'avez pas les permissions nécessaires.');
        }

        return $next($request);
    }
}