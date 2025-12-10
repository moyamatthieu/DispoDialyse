<?php

namespace App\Enums;

/**
 * Enum des types de dialyse
 */
enum TypeDialyseEnum: string
{
    case HEMODIALYSIS = 'hemodialysis';
    case HEMODIAFILTRATION = 'hemodiafiltration';
    case PERITONEAL = 'peritoneal';
    case HEMOFILTRATION = 'hemofiltration';

    /**
     * Obtenir le label en français
     */
    public function label(): string
    {
        return match($this) {
            self::HEMODIALYSIS => 'Hémodialyse',
            self::HEMODIAFILTRATION => 'Hémodiafiltration',
            self::PERITONEAL => 'Dialyse Péritonéale',
            self::HEMOFILTRATION => 'Hémofiltration',
        };
    }

    /**
     * Obtenir la description
     */
    public function description(): string
    {
        return match($this) {
            self::HEMODIALYSIS => 'Technique de dialyse standard par circulation sanguine extracorporelle',
            self::HEMODIAFILTRATION => 'Hémodialyse combinée avec ultrafiltration convective',
            self::PERITONEAL => 'Dialyse utilisant le péritoine comme membrane semi-perméable',
            self::HEMOFILTRATION => 'Épuration sanguine par convection pure',
        };
    }

    /**
     * Obtenir la durée moyenne de séance (en minutes)
     */
    public function averageDuration(): int
    {
        return match($this) {
            self::HEMODIALYSIS => 240,      // 4 heures
            self::HEMODIAFILTRATION => 240, // 4 heures
            self::PERITONEAL => 180,        // 3 heures
            self::HEMOFILTRATION => 240,    // 4 heures
        };
    }

    /**
     * Obtenir la couleur associée
     */
    public function color(): string
    {
        return match($this) {
            self::HEMODIALYSIS => 'blue',
            self::HEMODIAFILTRATION => 'purple',
            self::PERITONEAL => 'green',
            self::HEMOFILTRATION => 'indigo',
        };
    }

    /**
     * Obtenir tous les types pour sélection
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