<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécuter la migration.
     * 
     * Table users: Comptes utilisateurs avec authentification
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // Identifiants de connexion
            $table->string('username', 100)->unique()->comment('Identifiant de connexion');
            $table->string('email')->unique()->comment('Adresse email');
            $table->string('password')->comment('Mot de passe hashé');
            
            // Informations personnelles de base
            $table->string('first_name', 100)->comment('Prénom');
            $table->string('last_name', 100)->comment('Nom');
            $table->string('phone', 20)->nullable()->comment('Téléphone');
            
            // Rôle utilisateur (8 rôles définis)
            $table->enum('role', [
                'super_admin',
                'admin_fonctionnel',
                'cadre_sante',
                'medecin',
                'infirmier',
                'aide_soignant',
                'secretariat',
                'technicien'
            ])->default('infirmier')->comment('Rôle système de l\'utilisateur');
            
            // Statut et sécurité
            $table->boolean('is_active')->default(true)->comment('Compte actif');
            $table->boolean('mfa_enabled')->default(false)->comment('2FA activée');
            $table->string('mfa_secret')->nullable()->comment('Secret 2FA (chiffré)');
            
            // Vérification email
            $table->timestamp('email_verified_at')->nullable();
            
            // Tracking connexions
            $table->timestamp('last_login_at')->nullable()->comment('Dernière connexion');
            $table->string('last_login_ip', 45)->nullable()->comment('IP dernière connexion');
            
            // Token "Se souvenir de moi"
            $table->rememberToken();
            
            // Timestamps Laravel (created_at, updated_at)
            $table->timestamps();
            
            // Index pour performances
            $table->index('username');
            $table->index('email');
            $table->index('role');
            $table->index('is_active');
        });
        
        // Table pour réinitialisation mot de passe
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
        
        // Table pour sessions utilisateur
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Annuler la migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};