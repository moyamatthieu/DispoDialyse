<?php

namespace App\Providers;

use App\Models\Document;
use App\Models\Garde;
use App\Models\Message;
use App\Models\Personnel;
use App\Models\Reservation;
use App\Models\Transmission;
use App\Policies\DocumentPolicy;
use App\Policies\GardePolicy;
use App\Policies\MessagePolicy;
use App\Policies\PersonnelPolicy;
use App\Policies\ReservationPolicy;
use App\Policies\TransmissionPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Les mappings policy pour l'application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Personnel::class => PersonnelPolicy::class,
        Reservation::class => ReservationPolicy::class,
        Transmission::class => TransmissionPolicy::class,
        Garde::class => GardePolicy::class,
        Document::class => DocumentPolicy::class,
        Message::class => MessagePolicy::class,
    ];

    /**
     * Enregistrer tous les services d'authentification/autorisation.
     */
    public function boot(): void
    {
        //
    }
}