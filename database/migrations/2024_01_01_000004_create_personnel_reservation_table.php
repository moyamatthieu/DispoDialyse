<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécuter la migration.
     * 
     * Table pivot personnel_reservation: Assigner personnel aux séances (many-to-many)
     */
    public function up(): void
    {
        Schema::create('personnel_reservation', function (Blueprint $table) {
            $table->id();
            
            // Relations vers réservations et personnel
            $table->foreignId('reservation_id')
                ->constrained('reservations')
                ->onDelete('cascade')
                ->comment('Réservation/Séance');
            
            $table->foreignId('personnel_id')
                ->constrained('personnel')
                ->onDelete('cascade')
                ->comment('Personnel assigné');
            
            // Rôle du personnel dans cette séance spécifique
            $table->enum('role_in_session', [
                'lead_nurse',      // Infirmier référent
                'assistant_nurse', // Infirmier assistant
                'aide_soignant',   // Aide-soignant
                'physician'        // Médecin
            ])->default('assistant_nurse')->comment('Rôle dans la séance');
            
            // Timestamp de création uniquement (pas de updated_at)
            $table->timestamp('created_at')->useCurrent();
            
            // Contrainte unicité: un personnel ne peut être assigné qu'une fois par séance
            $table->unique(['reservation_id', 'personnel_id'], 'unique_personnel_reservation');
            
            // Index pour performances
            $table->index('reservation_id');
            $table->index('personnel_id');
        });
    }

    /**
     * Annuler la migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnel_reservation');
    }
};