<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécuter la migration.
     * 
     * Table audit_logs: Logs d'audit pour traçabilité complète (RGPD/Sécurité)
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            
            // Qui a effectué l'action?
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('Utilisateur (NULL si action système)');
            
            $table->string('ip_address', 45)->nullable()->comment('Adresse IP (support IPv6)');
            $table->text('user_agent')->nullable()->comment('User Agent navigateur');
            
            // Quelle action?
            $table->string('action', 100)->comment('Action effectuée (ex: "reservations.create")');
            $table->string('entity_type', 50)->nullable()->comment('Type d\'entité (ex: "Reservation")');
            $table->unsignedBigInteger('entity_id')->nullable()->comment('ID de l\'entité');
            
            // Détails de l'action
            $table->text('description')->nullable()->comment('Description lisible');
            $table->json('changes')->nullable()->comment('Changements effectués (before/after)');
            
            // Métadonnées
            $table->enum('severity', [
                'info',     // Information
                'warning',  // Avertissement
                'error',    // Erreur
                'critical'  // Critique
            ])->default('info')->comment('Niveau de gravité');
            
            // Timestamp uniquement (pas de updated_at)
            $table->timestamp('created_at')->useCurrent()->comment('Date de l\'action');
            
            // Index pour performances et recherches
            $table->index('user_id');
            $table->index('action');
            $table->index(['entity_type', 'entity_id'], 'idx_entite');
            $table->index('created_at');
            $table->index('severity');
            $table->index('ip_address');
        });
    }

    /**
     * Annuler la migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};