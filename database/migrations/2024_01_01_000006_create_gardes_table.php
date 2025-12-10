<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécuter la migration.
     * 
     * Table gardes: Planning de garde et astreintes
     */
    public function up(): void
    {
        Schema::create('gardes', function (Blueprint $table) {
            $table->id();
            
            // Personnel de garde
            $table->foreignId('personnel_id')
                ->constrained('personnel')
                ->onDelete('cascade')
                ->comment('Personnel de garde');
            
            // Période de garde
            $table->dateTime('start_datetime')->comment('Début de garde');
            $table->dateTime('end_datetime')->comment('Fin de garde');
            
            // Type de garde
            $table->enum('oncall_type', [
                'day_shift',      // Garde jour
                'night_shift',    // Garde nuit
                'weekend',        // Week-end
                'holiday',        // Jour férié
                'on_call'         // Astreinte
            ])->comment('Type de garde');
            
            // Catégorie professionnelle
            $table->enum('category', [
                'medical',    // Médical
                'nursing',    // Soins infirmiers
                'technical'   // Technique
            ])->default('nursing')->comment('Catégorie');
            
            // Statut
            $table->enum('status', [
                'scheduled',  // Planifiée
                'confirmed',  // Confirmée
                'completed',  // Terminée
                'cancelled'   // Annulée
            ])->default('scheduled')->comment('Statut');
            
            // Métadonnées
            $table->text('notes')->nullable()->comment('Notes');
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('Créé par');
            
            // Timestamps Laravel
            $table->timestamps();
            $table->softDeletes()->comment('Suppression logique');
            
            // Index pour performances
            $table->index('personnel_id');
            $table->index(['start_datetime', 'end_datetime'], 'idx_periode');
            $table->index('oncall_type');
            $table->index('status');
            $table->index('category');
        });
    }

    /**
     * Annuler la migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('gardes');
    }
};