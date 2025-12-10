<?php

namespace App\Enums;

/**
 * Enum des types de documents
 */
enum TypeDocumentEnum: string
{
    case PROTOCOL = 'protocol';
    case PROCEDURE = 'procedure';
    case TECHNICAL = 'technical';
    case TRAINING = 'training';
    case REGULATION = 'regulation';
    case CONTACT = 'contact';
    case PRACTICAL = 'practical';

    /**
     * Obtenir le label en français
     */
    public function label(): string
    {
        return match($this) {
            self::PROTOCOL => 'Protocole de soins',
            self::PROCEDURE => 'Procédure organisationnelle',
            self::TECHNICAL => 'Fiche technique',
            self::TRAINING => 'Formation',
            self::REGULATION => 'Réglementation',
            self::CONTACT => 'Contacts utiles',
            self::PRACTICAL => 'Informations pratiques',
        };
    }

    /**
     * Obtenir la description
     */
    public function description(): string
    {
        return match($this) {
            self::PROTOCOL => 'Protocoles de soins et guidelines médicaux',
            self::PROCEDURE => 'Procédures organisationnelles et administratives',
            self::TECHNICAL => 'Fiches techniques équipements et matériel',
            self::TRAINING => 'Supports de formation et modules pédagogiques',
            self::REGULATION => 'Documents réglementaires et normes',
            self::CONTACT => 'Annuaire et contacts d\'urgence',
            self::PRACTICAL => 'Guides pratiques et mémos',
        };
    }

    /**
     * Obtenir la couleur associée
     */
    public function color(): string
    {
        return match($this) {
            self::PROTOCOL => 'red',
            self::PROCEDURE => 'blue',
            self::TECHNICAL => 'gray',
            self::TRAINING => 'green',
            self::REGULATION => 'purple',
            self::CONTACT => 'yellow',
            self::PRACTICAL => 'indigo',
        };
    }

    /**
     * Obtenir l'icône associée
     */
    public function icon(): string
    {
        return match($this) {
            self::PROTOCOL => 'file-text',
            self::PROCEDURE => 'clipboard',
            self::TECHNICAL => 'tool',
            self::TRAINING => 'book-open',
            self::REGULATION => 'shield',
            self::CONTACT => 'phone',
            self::PRACTICAL => 'info',
        };
    }

    /**
     * Vérifier si le document nécessite une validation
     */
    public function requiresValidation(): bool
    {
        return in_array($this, [
            self::PROTOCOL,
            self::PROCEDURE,
            self::REGULATION
        ]);
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