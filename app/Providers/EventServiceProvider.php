<?php

namespace App\Providers;

use App\Events\ReservationCancelled;
use App\Events\ReservationCreated;
use App\Events\ReservationUpdated;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Les mappings d'événements/écouteurs pour l'application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        ReservationCreated::class => [
            // Ajouter ici les listeners pour les réservations créées
        ],

        ReservationUpdated::class => [
            // Ajouter ici les listeners pour les réservations mises à jour
        ],

        ReservationCancelled::class => [
            // Ajouter ici les listeners pour les réservations annulées
        ],
    ];

    /**
     * Enregistrer tous les événements pour votre application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Déterminer si les événements et les listeners doivent être découverts automatiquement.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}