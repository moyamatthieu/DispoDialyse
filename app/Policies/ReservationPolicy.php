<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;

/**
 * Policy pour les autorisations sur les réservations (planning)
 */
class ReservationPolicy
{
    /**
     * Déterminer si l'utilisateur peut voir toutes les réservations
     */
    public function viewAny(User $user): bool
    {
        return $user->can('planning.view');
    }

    /**
     * Déterminer si l'utilisateur peut voir une réservation
     */
    public function view(User $user, Reservation $reservation): bool
    {
        return $user->can('planning.view');
    }

    /**
     * Déterminer si l'utilisateur peut créer une réservation
     */
    public function create(User $user): bool
    {
        return $user->can('planning.create');
    }

    /**
     * Déterminer si l'utilisateur peut modifier une réservation
     */
    public function update(User $user, Reservation $reservation): bool
    {
        // Peut modifier si a la permission ET (est admin OU a créé la réservation)
        return $user->can('planning.edit') && 
               ($user->isAdmin() || $reservation->created_by === $user->id);
    }

    /**
     * Déterminer si l'utilisateur peut supprimer une réservation
     */
    public function delete(User $user, Reservation $reservation): bool
    {
        // Peut supprimer si a la permission ET (est admin OU a créé la réservation)
        return $user->can('planning.delete') && 
               ($user->isAdmin() || $reservation->created_by === $user->id);
    }

    /**
     * Déterminer si l'utilisateur peut annuler une réservation
     */
    public function cancel(User $user, Reservation $reservation): bool
    {
        return $user->can('planning.edit') && 
               ($user->isAdmin() || $reservation->created_by === $user->id);
    }
}