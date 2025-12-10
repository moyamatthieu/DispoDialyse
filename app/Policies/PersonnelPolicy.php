<?php

namespace App\Policies;

use App\Models\Personnel;
use App\Models\User;

/**
 * Policy pour les autorisations sur le personnel (annuaire)
 */
class PersonnelPolicy
{
    /**
     * Déterminer si l'utilisateur peut voir tous les personnels
     */
    public function viewAny(User $user): bool
    {
        return $user->can('personnel.view');
    }

    /**
     * Déterminer si l'utilisateur peut voir un personnel
     */
    public function view(User $user, Personnel $personnel): bool
    {
        return $user->can('personnel.view');
    }

    /**
     * Déterminer si l'utilisateur peut créer un personnel
     */
    public function create(User $user): bool
    {
        return $user->can('personnel.create');
    }

    /**
     * Déterminer si l'utilisateur peut modifier un personnel
     */
    public function update(User $user, Personnel $personnel): bool
    {
        // Peut modifier si a la permission ET (est admin OU modifie son propre profil)
        return $user->can('personnel.edit') && 
               ($user->isAdmin() || $personnel->user_id === $user->id);
    }

    /**
     * Déterminer si l'utilisateur peut supprimer un personnel
     */
    public function delete(User $user, Personnel $personnel): bool
    {
        // Seuls les admins peuvent supprimer
        return $user->can('personnel.delete') && $user->isAdmin();
    }
}