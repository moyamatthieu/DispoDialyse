<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de vérification des permissions
 * 
 * Vérifie si l'utilisateur possède la permission requise via Spatie Permission
 */
class CheckPermission
{
    /**
     * Gérer une requête entrante
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission Nom de la permission requise
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!$request->user()) {
            abort(401, 'Non authentifié');
        }

        $user = $request->user();

        // Vérifier si l'utilisateur a la permission
        if (!$user->can($permission)) {
            // Log d'audit pour tentative d'accès non autorisé
            activity()
                ->causedBy($user)
                ->withProperties([
                    'url' => $request->url(),
                    'required_permission' => $permission,
                    'user_role' => $user->role->value,
                    'ip' => $request->ip(),
                ])
                ->log('Tentative d\'accès sans permission');

            abort(403, 'Accès refusé. Permission insuffisante : ' . $permission);
        }

        return $next($request);
    }
}