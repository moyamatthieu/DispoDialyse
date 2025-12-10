<?php

namespace App\Helpers;

use App\Enums\RoleEnum;
use App\Models\User;

/**
 * Fonctions helper pour l'authentification et les autorisations
 */
class AuthHelper
{
    /**
     * Vérifier si l'utilisateur authentifié a un rôle spécifique
     */
    public static function hasRole(string|array $roles): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $userRole = auth()->user()->role->value;
        
        if (is_array($roles)) {
            return in_array($userRole, $roles);
        }

        return $userRole === $roles;
    }

    /**
     * Vérifier si l'utilisateur est administrateur
     */
    public static function isAdmin(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Vérifier si l'utilisateur est personnel médical
     */
    public static function isMedical(): bool
    {
        return auth()->check() && auth()->user()->isMedical();
    }

    /**
     * Obtenir le nom du rôle de l'utilisateur
     */
    public static function roleName(): ?string
    {
        return auth()->check() ? auth()->user()->role->label() : null;
    }

    /**
     * Obtenir la couleur du rôle pour l'UI
     */
    public static function roleColor(): ?string
    {
        return auth()->check() ? auth()->user()->role->color() : null;
    }

    /**
     * Vérifier si l'utilisateur peut gérer les utilisateurs
     */
    public static function canManageUsers(): bool
    {
        return auth()->check() && auth()->user()->can('users.manage');
    }

    /**
     * Vérifier si l'utilisateur peut voir les audits
     */
    public static function canViewAudit(): bool
    {
        return auth()->check() && auth()->user()->can('audit.view');
    }

    /**
     * Obtenir tous les rôles disponibles pour un select
     */
    public static function availableRoles(): array
    {
        return RoleEnum::options();
    }

    /**
     * Formater le nom complet de l'utilisateur
     */
    public static function fullName(): ?string
    {
        return auth()->check() ? auth()->user()->full_name : null;
    }

    /**
     * Obtenir les initiales de l'utilisateur
     */
    public static function initials(): ?string
    {
        return auth()->check() ? auth()->user()->initials : null;
    }
}