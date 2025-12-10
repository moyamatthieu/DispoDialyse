<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécuter la migration.
     * 
     * Table documents: Référentiel documentaire (protocoles, procédures, formations)
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            
            // Identification
            $table->string('title')->comment('Titre du document');
            $table->text('description')->nullable()->comment('Description');
            
            // Catégorisation
            $table->enum('category', [
                'protocol',       // Protocole de soins
                'procedure',      // Procédure organisationnelle
                'technical',      // Fiche technique
                'training',       // Formation
                'regulation',     // Réglementation
                'contact',        // Contacts utiles
                'practical'       // Infos pratiques
            ])->comment('Catégorie');
            
            // Fichier
            $table->string('file_path', 500)->comment('Chemin fichier');
            $table->string('file_name')->comment('Nom fichier original');
            $table->string('file_type', 50)->nullable()->comment('Type MIME');
            $table->unsignedInteger('file_size')->nullable()->comment('Taille en octets');
            
            // Versioning
            $table->string('version', 20)->default('1.0')->comment('Version');
            $table->enum('status', [
                'draft',      // Brouillon
                'published',  // Publié
                'archived'    // Archivé
            ])->default('draft')->comment('Statut de publication');
            
            // Métadonnées
            $table->json('tags')->nullable()->comment('Tags (JSON array)');
            $table->string('author', 100)->nullable()->comment('Auteur');
            $table->date('published_at')->nullable()->comment('Date de publication');
            $table->date('expires_at')->nullable()->comment('Date d\'expiration');
            
            // Permissions d'accès (JSON array de rôles autorisés)
            $table->json('restricted_to_roles')->nullable()->comment('Rôles autorisés (NULL = tous)');
            
            // Statistiques d'utilisation
            $table->unsignedInteger('view_count')->default(0)->comment('Nombre de vues');
            $table->unsignedInteger('download_count')->default(0)->comment('Nombre de téléchargements');
            
            // Auteur système
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null')
                ->comment('Créé par');
            
            // Timestamps Laravel
            $table->timestamps();
            $table->softDeletes()->comment('Suppression logique');
            
            // Index pour performances
            $table->index('category');
            $table->index('status');
            $table->index('published_at');
            $table->index('expires_at');
        });
        
        // Index full-text pour recherche (seulement pour MySQL/MariaDB)
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('documents', function (Blueprint $table) {
                $table->fullText(['title', 'description', 'author'], 'idx_recherche_documents');
            });
        }
    }

    /**
     * Annuler la migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};