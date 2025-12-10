<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécuter la migration.
     * 
     * Table reservations: Séances de dialyse planifiées (cœur du système)
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            
            // Salle assignée
            $table->foreignId('salle_id')
                ->constrained('salles')
                ->onDelete('cascade')
                ->comment('Salle de dialyse assignée');
            
            // Horaires de la séance
            $table->dateTime('start_time')->comment('Début de séance');
            $table->dateTime('end_time')->comment('Fin de séance');
            
            // Patient (anonymisé pour RGPD)
            $table->string('patient_reference', 50)->nullable()->comment('Référence patient anonymisée');
            $table->string('patient_initials', 10)->nullable()->comment('Initiales patient');
            
            // Type de dialyse
            $table->enum('dialysis_type', [
                'hemodialysis',
                'hemodiafiltration',
                'peritoneal',
                'hemofiltration'
            ])->default('hemodialysis')->comment('Type de dialyse');
            
            // Statut de la réservation
            $table->enum('status', [
                'scheduled',   // Planifiée
                'in_progress', // En cours
                'completed',   // Terminée
                'cancelled',   // Annulée
                'no_show'      // Patient absent
            ])->default('scheduled')->comment('Statut de la séance');
            
            // Informations opérationnelles
            $table->text('notes')->nullable()->comment('Notes et observations');
            $table->text('special_requirements')->nullable()->comment('Précautions particulières');
            
            // Métadonnées d'annulation
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('Créé par');
            $table->dateTime('cancelled_at')->nullable()->comment('Date d\'annulation');
            $table->text('cancellation_reason')->nullable()->comment('Raison annulation');
            
            // Timestamps Laravel
            $table->timestamps();
            $table->softDeletes()->comment('Suppression logique');
            
            // Index pour performances (détection conflits et recherches)
            $table->index(['salle_id', 'start_time', 'end_time'], 'idx_salle_horaires');
            $table->index('start_time');
            $table->index('status');
            $table->index('patient_reference');
            $table->index('dialysis_type');
        });
    }

    /**
     * Annuler la migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};