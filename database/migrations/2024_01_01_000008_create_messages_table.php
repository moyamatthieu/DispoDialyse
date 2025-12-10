<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécuter la migration.
     * 
     * Table messages: Messagerie interne entre utilisateurs
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            
            // Expéditeur et destinataire
            $table->foreignId('sender_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Expéditeur');
            
            $table->foreignId('recipient_id')
                ->constrained('users')
                ->onDelete('cascade')
                ->comment('Destinataire');
            
            // Contenu du message
            $table->string('subject')->comment('Sujet');
            $table->text('body')->comment('Corps du message');
            
            // Statut de lecture
            $table->boolean('is_read')->default(false)->comment('Message lu');
            $table->dateTime('read_at')->nullable()->comment('Date de lecture');
            
            // Thread de conversation (réponses)
            $table->foreignId('parent_message_id')
                ->nullable()
                ->constrained('messages')
                ->onDelete('set null')
                ->comment('Message parent (NULL si initial)');
            
            // Suppression soft (RGPD)
            $table->boolean('deleted_by_sender')->default(false)->comment('Supprimé par expéditeur');
            $table->boolean('deleted_by_recipient')->default(false)->comment('Supprimé par destinataire');
            
            // Timestamps Laravel
            $table->timestamps();
            
            // Index pour performances
            $table->index(['recipient_id', 'is_read'], 'idx_destinataire_lu');
            $table->index('sender_id');
            $table->index('parent_message_id');
            $table->index('created_at');
            $table->index(['deleted_by_sender', 'deleted_by_recipient'], 'idx_suppression');
        });
    }

    /**
     * Annuler la migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};