<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécuter la migration.
     * 
     * Table transmissions: Transmissions d'informations patients
     */
    public function up(): void
    {
        Schema::create('transmissions', function (Blueprint $table) {
            $table->id();
            
            // Lien optionnel vers une réservation
            $table->foreignId('reservation_id')
                ->nullable()
                ->constrained('reservations')
                ->onDelete('set null')
                ->comment('Réservation associée (optionnel)');
            
            // Patient concerné
            $table->string('patient_reference', 50)->comment('Référence patient');
            
            // Classification de la transmission
            $table->enum('category', [
                'logistique',
                'comportement',
                'clinique',
                'precaution'
            ])->comment('Catégorie de transmission');
            
            $table->enum('priority', [
                'normale',
                'importante',
                'urgente'
            ])->default('normale')->comment('Priorité');
            
            // Contenu
            $table->string('title')->comment('Titre de la transmission');
            $table->text('content')->comment('Contenu détaillé');
            
            // Données cliniques (JSON pour flexibilité)
            $table->json('vital_signs')->nullable()->comment('Signes vitaux (JSON)');
            
            // Système d'alertes
            $table->boolean('has_alert')->default(false)->comment('Transmission avec alerte');
            $table->boolean('alert_acknowledged')->default(false)->comment('Alerte accusée réception');
            $table->foreignId('alert_acknowledged_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('Alerte accusée par');
            $table->dateTime('alert_acknowledged_at')->nullable()->comment('Date accusé réception');
            
            // Auteur de la transmission
            $table->foreignId('created_by')
                ->constrained('users')
                ->onDelete('restrict')
                ->comment('Créé par (obligatoire)');
            
            // Archivage
            $table->boolean('is_archived')->default(false)->comment('Transmission archivée');
            $table->dateTime('archived_at')->nullable()->comment('Date archivage');
            
            // Timestamps Laravel
            $table->timestamps();
            $table->softDeletes()->comment('Suppression logique');
            
            // Index pour performances
            $table->index('patient_reference');
            $table->index('priority');
            $table->index('category');
            $table->index('created_at');
            $table->index(['has_alert', 'alert_acknowledged'], 'idx_alertes');
            $table->index('is_archived');
        });
        
        // Index full-text pour recherche (seulement pour MySQL/MariaDB)
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('transmissions', function (Blueprint $table) {
                $table->fullText(['title', 'content'], 'idx_recherche_transmissions');
            });
        }
    }

    /**
     * Annuler la migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('transmissions');
    }
};