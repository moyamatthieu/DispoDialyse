<?php

namespace App\Policies;

use App\Models\Garde;
use App\Models\User;

/**
 * Policy pour les autorisations sur les gardes
 */
class GardePolicy
{
    /**
     * Déterminer si l'utilisateur peut voir toutes les gardes
     */
    public function viewAny(User $user): bool
    {
        return $user->can('gardes.view');
    }

    /**
     * Déterminer si l'utilisateur peut voir une garde
     */
    public function view(User $user, Garde $garde): bool
    {
        return $user->can('gardes.view');
    }

    /**
     * Déterminer si l'utilisateur peut créer une garde
     */
    public function create(User $user): bool
    {
        return $user->can('gardes.manage');
    }

    /**
     * Déterminer si l'utilisateur peut modifier une garde
     */
    public function update(User $user, Garde $garde): bool
    {
        return $user->can('gardes.manage');
    }

    /**
     * Déterminer si l'utilisateur peut supprimer une garde
     */
    public function delete(User $user, Garde $garde): bool
    {
        // Seuls les admins et cadres de santé peuvent supprimer
        return $user->can('gardes.manage') && 
               ($user->isAdmin() || $user->role->value === 'cadre_sante');
    }
}