<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Enregistrer tous les services de l'application.
     */
    public function register(): void
    {
        //
    }

    /**
     * Démarrer tous les services de l'application.
     */
    public function boot(): void
    {
        // Configuration pour la longueur des chaînes par défaut pour les migrations
        Schema::defaultStringLength(191);

        // Prévenir le lazy loading en mode non-production pour détecter les problèmes N+1
        Model::preventLazyLoading(! $this->app->isProduction());

        // Désactiver le mass assignment en production pour plus de sécurité
        Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());

        // Empêcher l'accès à des attributs manquants
        Model::preventAccessingMissingAttributes(! $this->app->isProduction());
    }
}