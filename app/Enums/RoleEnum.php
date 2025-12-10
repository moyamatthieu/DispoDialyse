<?php

namespace App\Enums;

/**
 * Enum des rôles utilisateurs du système DispoDialyse
 * 
 * 8 rôles hiérarchiques définis pour la gestion des permissions
 */
enum RoleEnum: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN_FONCTIONNEL = 'admin_fonctionnel';
    case CADRE_SANTE = 'cadre_sante';
    case MEDECIN = 'medecin';
    case INFIRMIER = 'infirmier';
    case AIDE_SOIGNANT = 'aide_soignant';
    case SECRETARIAT = 'secretariat';
    case TECHNICIEN = 'technicien';

    /**
     * Obtenir le label en français du rôle
     */
    public function label(): string
    {
        return match($this) {
            self::SUPER_ADMIN => 'Super Administrateur',
            self::ADMIN_FONCTIONNEL => 'Administrateur Fonctionnel',
            self::CADRE_SANTE => 'Cadre de Santé',
            self::MEDECIN => 'Médecin',
            self::INFIRMIER => 'Infirmier(ère)',
            self::AIDE_SOIGNANT => 'Aide-Soignant(e)',
            self::SECRETARIAT => 'Secrétariat',
            self::TECHNICIEN => 'Technicien',
        };
    }

    /**
     * Obtenir la description du rôle
     */
    public function description(): string
    {
        return match($this) {
            self::SUPER_ADMIN => 'Accès total au système, gestion des utilisateurs et configuration',
            self::ADMIN_FONCTIONNEL => 'Gestion administrative et organisationnelle du service',
            self::CADRE_SANTE => 'Supervision du personnel soignant et coordination des plannings',
            self::MEDECIN => 'Gestion médicale des patients et validation des protocoles',
            self::INFIRMIER => 'Soins directs aux patients et gestion des séances de dialyse',
            self::AIDE_SOIGNANT => 'Assistance aux soins et support logistique',
            self::SECRETARIAT => 'Gestion administrative et documentation',
            self::TECHNICIEN => 'Maintenance technique des équipements',
        };
    }

    /**
     * Vérifier si le rôle est administratif
     */
    public function isAdmin(): bool
    {
        return in_array($this, [
            self::SUPER_ADMIN,
            self::ADMIN_FONCTIONNEL
        ]);
    }

    /**
     * Vérifier si le rôle est médical
     */
    public function isMedical(): bool
    {
        return in_array($this, [
            self::MEDECIN,
            self::INFIRMIER,
            self::AIDE_SOIGNANT
        ]);
    }

    /**
     * Vérifier si le rôle peut gérer le planning
     */
    public function canManagePlanning(): bool
    {
        return in_array($this, [
            self::SUPER_ADMIN,
            self::ADMIN_FONCTIONNEL,
            self::CADRE_SANTE,
            self::MEDECIN,
            self::INFIRMIER
        ]);
    }

    /**
     * Vérifier si le rôle peut voir les transmissions patients
     */
    public function canViewTransmissions(): bool
    {
        return in_array($this, [
            self::SUPER_ADMIN,
            self::ADMIN_FONCTIONNEL,
            self::CADRE_SANTE,
            self::MEDECIN,
            self::INFIRMIER
        ]);
    }

    /**
     * Obtenir tous les rôles disponibles
     * 
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn($case) => $case->label(), self::cases())
        );
    }

    /**
     * Obtenir la couleur associée au rôle (pour UI)
     */
    public function color(): string
    {
        return match($this) {
            self::SUPER_ADMIN => 'red',
            self::ADMIN_FONCTIONNEL => 'purple',
            self::CADRE_SANTE => 'indigo',
            self::MEDECIN => 'blue',
            self::INFIRMIER => 'green',
            self::AIDE_SOIGNANT => 'teal',
            self::SECRETARIAT => 'yellow',
            self::TECHNICIEN => 'gray',
        };
    }
}