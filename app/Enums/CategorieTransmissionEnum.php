<?php

namespace App\Enums;

/**
 * Enum des catégories de transmission patient
 */
enum CategorieTransmissionEnum: string
{
    case LOGISTIQUE = 'logistique';
    case COMPORTEMENT = 'comportement';
    case CLINIQUE = 'clinique';
    case PRECAUTION = 'precaution';

    /**
     * Obtenir le label en français
     */
    public function label(): string
    {
        return match($this) {
            self::LOGISTIQUE => 'Logistique',
            self::COMPORTEMENT => 'Comportement',
            self::CLINIQUE => 'Clinique',
            self::PRECAUTION => 'Précaution',
        };
    }

    /**
     * Obtenir la description
     */
    public function description(): string
    {
        return match($this) {
            self::LOGISTIQUE => 'Organisation, matériel, horaires, transport',
            self::COMPORTEMENT => 'Attitude, humeur, communication, relation',
            self::CLINIQUE => 'Signes vitaux, symptômes, état de santé',
            self::PRECAUTION => 'Consignes spéciales, isolement, allergies',
        };
    }

    /**
     * Obtenir la couleur associée
     */
    public function color(): string
    {
        return match($this) {
            self::LOGISTIQUE => 'blue',
            self::COMPORTEMENT => 'purple',
            self::CLINIQUE => 'red',
            self::PRECAUTION => 'orange',
        };
    }

    /**
     * Obtenir l'icône associée
     */
    public function icon(): string
    {
        return match($this) {
            self::LOGISTIQUE => 'package',
            self::COMPORTEMENT => 'user',
            self::CLINIQUE => 'activity',
            self::PRECAUTION => 'shield',
        };
    }

    /**
     * Obtenir toutes les catégories pour sélection
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
}