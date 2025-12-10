<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider pour les directives Blade personnalisées
 */
class BladeServiceProvider extends ServiceProvider
{
    /**
     * Enregistrer les services
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap des services
     */
    public function boot(): void
    {
        // Enregistrer les composants Blade anonymes
        Blade::anonymousComponentPath(resource_path('views/layouts'), 'layouts');
        Blade::anonymousComponentPath(resource_path('views/components'), 'components');

        // Directive @role - Vérifier si l'utilisateur a un rôle spécifique
        Blade::if('role', function (string|array $roles) {
            if (!auth()->check()) {
                return false;
            }

            $userRole = auth()->user()->role->value;
            
            if (is_array($roles)) {
                return in_array($userRole, $roles);
            }

            return $userRole === $roles;
        });

        // Directive @hasrole - Alias de @role
        Blade::if('hasrole', function (string|array $roles) {
            if (!auth()->check()) {
                return false;
            }

            $userRole = auth()->user()->role->value;
            
            if (is_array($roles)) {
                return in_array($userRole, $roles);
            }

            return $userRole === $roles;
        });

        // Directive @admin - Vérifier si l'utilisateur est administrateur
        Blade::if('admin', function () {
            return auth()->check() && auth()->user()->isAdmin();
        });

        // Directive @medical - Vérifier si l'utilisateur est personnel médical
        Blade::if('medical', function () {
            return auth()->check() && auth()->user()->isMedical();
        });

        // Directive @canmanageplanning - Vérifier si l'utilisateur peut gérer le planning
        Blade::if('canmanageplanning', function () {
            return auth()->check() && auth()->user()->canManagePlanning();
        });

        // Directive @canviewtransmissions - Vérifier si l'utilisateur peut voir les transmissions
        Blade::if('canviewtransmissions', function () {
            return auth()->check() && auth()->user()->canViewTransmissions();
        });
    }
}