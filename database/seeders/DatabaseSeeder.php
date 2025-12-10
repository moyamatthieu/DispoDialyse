<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder principal de la base de données
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Exécuter les seeders de la base de données
     */
    public function run(): void
    {
        $this->command->info('🚀 Démarrage du seeding de la base de données DispoDialyse...');
        $this->command->newLine();

        // 1. Créer les rôles et permissions (doit être fait en premier)
        $this->command->info('📝 Création des rôles et permissions...');
        $this->call(RolePermissionSeeder::class);
        $this->command->newLine();

        // 2. Créer les utilisateurs de test
        $this->command->info('👥 Création des utilisateurs de test...');
        $this->call(UserSeeder::class);
        $this->command->newLine();

        // Message de fin
        $this->command->info('✅ Seeding terminé avec succès !');
        $this->command->newLine();
        $this->command->info('📋 Récapitulatif :');
        $this->command->info('   • 8 rôles créés avec leurs permissions');
        $this->command->info('   • 10 utilisateurs de test créés');
        $this->command->newLine();
        $this->command->warn('⚠️  IMPORTANT : Les mots de passe par défaut sont "Password123!"');
        $this->command->warn('⚠️  Changez-les immédiatement en production !');
        $this->command->newLine();
        $this->command->info('🔐 Comptes disponibles :');
        $this->command->table(
            ['Rôle', 'Email', 'Mot de passe'],
            [
                ['Super Admin', 'admin@dispodialyse.fr', 'Password123!'],
                ['Admin Fonctionnel', 'admin.fonctionnel@dispodialyse.fr', 'Password123!'],
                ['Cadre de Santé', 'cadre@dispodialyse.fr', 'Password123!'],
                ['Médecin', 'medecin@dispodialyse.fr', 'Password123!'],
                ['Infirmier', 'infirmier@dispodialyse.fr', 'Password123!'],
                ['Aide-Soignant', 'aidesoignant@dispodialyse.fr', 'Password123!'],
                ['Secrétariat', 'secretariat@dispodialyse.fr', 'Password123!'],
                ['Technicien', 'technicien@dispodialyse.fr', 'Password123!'],
            ]
        );
    }
}