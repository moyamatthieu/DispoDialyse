<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécuter la migration.
     * 
     * Table salles: Salles de dialyse et leurs caractéristiques
     */
    public function up(): void
    {
        Schema::create('salles', function (Blueprint $table) {
            $table->id();
            
            // Identification de la salle
            $table->string('name', 50)->unique()->comment('Nom de la salle (ex: "Salle A1")');
            $table->string('code', 20)->unique()->nullable()->comment('Code court (ex: "SA1")');
            
            // Localisation
            $table->string('floor', 20)->nullable()->comment('Étage');
            $table->string('building', 50)->nullable()->comment('Bâtiment');
            
            // Caractéristiques
            $table->integer('capacity')->default(1)->comment('Nombre de postes de dialyse');
            $table->boolean('is_isolation')->default(false)->comment('Salle d\'isolement');
            
            // Équipements (JSON array pour flexibilité)
            $table->json('equipment')->nullable()->comment('Liste équipements (JSON array)');
            
            // Statut
            $table->boolean('is_active')->default(true)->comment('Salle active/utilisable');
            
            // Notes et remarques
            $table->text('notes')->nullable()->comment('Notes et remarques');
            
            // Timestamps Laravel
            $table->timestamps();
            $table->softDeletes()->comment('Suppression logique');
            
            // Index pour performances
            $table->index('is_active');
            $table->index('name');
            $table->index('is_isolation');
        });
    }

    /**
     * Annuler la migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('salles');
    }
};