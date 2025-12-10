<?php

namespace App\Enums;

/**
 * Enum des statuts de réservation (séances de dialyse)
 */
enum StatutReservationEnum: string
{
    case SCHEDULED = 'scheduled';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case NO_SHOW = 'no_show';

    /**
     * Obtenir le label en français
     */
    public function label(): string
    {
        return match($this) {
            self::SCHEDULED => 'Planifiée',
            self::IN_PROGRESS => 'En cours',
            self::COMPLETED => 'Terminée',
            self::CANCELLED => 'Annulée',
            self::NO_SHOW => 'Patient absent',
        };
    }

    /**
     * Obtenir la couleur du badge
     */
    public function color(): string
    {
        return match($this) {
            self::SCHEDULED => 'blue',
            self::IN_PROGRESS => 'green',
            self::COMPLETED => 'gray',
            self::CANCELLED => 'red',
            self::NO_SHOW => 'orange',
        };
    }

    /**
     * Obtenir l'icône associée
     */
    public function icon(): string
    {
        return match($this) {
            self::SCHEDULED => 'calendar',
            self::IN_PROGRESS => 'clock',
            self::COMPLETED => 'check-circle',
            self::CANCELLED => 'x-circle',
            self::NO_SHOW => 'alert-circle',
        };
    }

    /**
     * Vérifier si le statut est modifiable
     */
    public function isEditable(): bool
    {
        return in_array($this, [
            self::SCHEDULED,
            self::IN_PROGRESS
        ]);
    }

    /**
     * Vérifier si le statut est final
     */
    public function isFinal(): bool
    {
        return in_array($this, [
            self::COMPLETED,
            self::CANCELLED,
            self::NO_SHOW
        ]);
    }

    /**
     * Obtenir tous les statuts pour sélection
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