<?php

namespace App\Enums;

/**
 * Enum des priorités de transmission patient
 */
enum PrioriteTransmissionEnum: string
{
    case NORMALE = 'normale';
    case IMPORTANTE = 'importante';
    case URGENTE = 'urgente';

    /**
     * Obtenir le label en français
     */
    public function label(): string
    {
        return match($this) {
            self::NORMALE => 'Normale',
            self::IMPORTANTE => 'Importante',
            self::URGENTE => 'Urgente',
        };
    }

    /**
     * Obtenir la couleur du badge
     */
    public function color(): string
    {
        return match($this) {
            self::NORMALE => 'gray',
            self::IMPORTANTE => 'yellow',
            self::URGENTE => 'red',
        };
    }

    /**
     * Obtenir l'icône associée
     */
    public function icon(): string
    {
        return match($this) {
            self::NORMALE => 'info',
            self::IMPORTANTE => 'alert-triangle',
            self::URGENTE => 'alert-circle',
        };
    }

    /**
     * Vérifier si la priorité nécessite une notification immédiate
     */
    public function requiresImmediateNotification(): bool
    {
        return $this === self::URGENTE;
    }

    /**
     * Obtenir le délai de réponse attendu (en heures)
     */
    public function expectedResponseTime(): int
    {
        return match($this) {
            self::NORMALE => 24,     // 24 heures
            self::IMPORTANTE => 4,   // 4 heures
            self::URGENTE => 1,      // 1 heure
        };
    }

    /**
     * Obtenir tous les niveaux de priorité pour sélection
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