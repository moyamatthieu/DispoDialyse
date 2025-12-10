<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécuter la migration.
     * 
     * Table personnel: Profils détaillés du personnel (annuaire)
     */
    public function up(): void
    {
        Schema::create('personnel', function (Blueprint $table) {
            $table->id();
            
            // Lien vers compte utilisateur (nullable pour personnel externe)
            $table->foreignId('user_id')
                ->nullable()
                ->unique()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('Lien vers compte utilisateur (NULL si externe)');
            
            // Identité
            $table->string('first_name', 100)->comment('Prénom');
            $table->string('last_name', 100)->comment('Nom');
            $table->string('photo_url', 500)->nullable()->comment('URL photo de profil');
            
            // Informations de contact
            $table->string('phone_office', 20)->nullable()->comment('Téléphone bureau');
            $table->string('phone_mobile', 20)->nullable()->comment('Téléphone mobile');
            $table->string('phone_pager', 20)->nullable()->comment('Bipeur/Pager');
            $table->string('email_pro')->nullable()->comment('Email professionnel');
            $table->string('extension', 10)->nullable()->comment('Numéro de poste');
            
            // Informations professionnelles
            $table->string('job_title', 150)->comment('Fonction/Titre du poste');
            $table->string('specialty', 100)->nullable()->comment('Spécialité médicale');
            $table->string('department', 100)->default('Dialyse')->comment('Service/Département');
            $table->enum('employment_type', ['full_time', 'part_time', 'contractor'])
                ->default('full_time')
                ->comment('Type de contrat');
            
            // Compétences et qualifications (JSON pour flexibilité)
            $table->json('qualifications')->nullable()->comment('Qualifications (JSON array)');
            $table->json('languages')->nullable()->comment('Langues parlées (JSON array)');
            $table->json('certifications')->nullable()->comment('Certifications (JSON array)');
            
            // Disponibilité
            $table->boolean('is_active')->default(true)->comment('Personnel actif');
            $table->date('hire_date')->nullable()->comment('Date d\'embauche');
            $table->date('leave_date')->nullable()->comment('Date de départ');
            
            // Timestamps Laravel
            $table->timestamps();
            $table->softDeletes()->comment('Suppression logique (RGPD)');
            
            // Index pour performances
            $table->index(['last_name', 'first_name'], 'idx_nom_prenom');
            $table->index('is_active');
            $table->index('department');
        });
        
        // Index full-text pour recherche (seulement pour MySQL/MariaDB)
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('personnel', function (Blueprint $table) {
                $table->fullText(['first_name', 'last_name', 'job_title', 'specialty'], 'idx_recherche_personnel');
            });
        }
    }

    /**
     * Annuler la migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnel');
    }
};