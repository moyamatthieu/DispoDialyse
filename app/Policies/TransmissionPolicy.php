<?php

namespace App\Policies;

use App\Models\Transmission;
use App\Models\User;

/**
 * Policy pour les autorisations sur les transmissions patients
 */
class TransmissionPolicy
{
    /**
     * Déterminer si l'utilisateur peut voir toutes les transmissions
     */
    public function viewAny(User $user): bool
    {
        return $user->can('transmissions.view');
    }

    /**
     * Déterminer si l'utilisateur peut voir une transmission
     */
    public function view(User $user, Transmission $transmission): bool
    {
        return $user->can('transmissions.view');
    }

    /**
     * Déterminer si l'utilisateur peut créer une transmission
     */
    public function create(User $user): bool
    {
        return $user->can('transmissions.create');
    }

    /**
     * Déterminer si l'utilisateur peut modifier une transmission
     */
    public function update(User $user, Transmission $transmission): bool
    {
        // Peut modifier si a la permission ET (est admin/médecin OU a créé la transmission dans les 24h)
        $canEditOwn = $transmission->created_by === $user->id && 
                      $transmission->created_at->diffInHours(now()) < 24;
        
        return $user->can('transmissions.edit') && 
               ($user->isAdmin() || $user->role->isMedical() || $canEditOwn);
    }

    /**
     * Déterminer si l'utilisateur peut supprimer une transmission
     */
    public function delete(User $user, Transmission $transmission): bool
    {
        // Seuls les admins et médecins peuvent supprimer
        return $user->can('transmissions.delete') && 
               ($user->isAdmin() || $user->role->value === 'medecin');
    }

    /**
     * Déterminer si l'utilisateur peut accuser réception d'une transmission
     */
    public function acknowledge(User $user, Transmission $transmission): bool
    {
        // Tout utilisateur avec droit de lecture peut accuser réception
        return $user->can('transmissions.view') && 
               $transmission->created_by !== $user->id;
    }
}