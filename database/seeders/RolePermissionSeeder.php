<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Seeder pour les rôles et permissions du système
 * 
 * Crée les 8 rôles définis et attribue les permissions appropriées
 */
class RolePermissionSeeder extends Seeder
{
    /**
     * Exécuter le seeder
     */
    public function run(): void
    {
        // Réinitialiser le cache des permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Créer toutes les permissions
        $permissions = $this->createPermissions();

        // Créer les rôles et attribuer les permissions
        $this->createRolesWithPermissions($permissions);
    }

    /**
     * Créer toutes les permissions du système
     */
    private function createPermissions(): array
    {
        $permissions = [];

        // Permissions Planning
        $permissions['planning'] = [
            Permission::create(['name' => 'planning.view', 'guard_name' => 'web']),
            Permission::create(['name' => 'planning.create', 'guard_name' => 'web']),
            Permission::create(['name' => 'planning.edit', 'guard_name' => 'web']),
            Permission::create(['name' => 'planning.delete', 'guard_name' => 'web']),
        ];

        // Permissions Personnel (annuaire)
        $permissions['personnel'] = [
            Permission::create(['name' => 'personnel.view', 'guard_name' => 'web']),
            Permission::create(['name' => 'personnel.create', 'guard_name' => 'web']),
            Permission::create(['name' => 'personnel.edit', 'guard_name' => 'web']),
            Permission::create(['name' => 'personnel.delete', 'guard_name' => 'web']),
        ];

        // Permissions Transmissions
        $permissions['transmissions'] = [
            Permission::create(['name' => 'transmissions.view', 'guard_name' => 'web']),
            Permission::create(['name' => 'transmissions.create', 'guard_name' => 'web']),
            Permission::create(['name' => 'transmissions.edit', 'guard_name' => 'web']),
            Permission::create(['name' => 'transmissions.delete', 'guard_name' => 'web']),
        ];

        // Permissions Gardes
        $permissions['gardes'] = [
            Permission::create(['name' => 'gardes.view', 'guard_name' => 'web']),
            Permission::create(['name' => 'gardes.manage', 'guard_name' => 'web']),
        ];

        // Permissions Documents
        $permissions['documents'] = [
            Permission::create(['name' => 'documents.view', 'guard_name' => 'web']),
            Permission::create(['name' => 'documents.upload', 'guard_name' => 'web']),
            Permission::create(['name' => 'documents.edit', 'guard_name' => 'web']),
            Permission::create(['name' => 'documents.delete', 'guard_name' => 'web']),
        ];

        // Permissions Messagerie
        $permissions['messages'] = [
            Permission::create(['name' => 'messages.view', 'guard_name' => 'web']),
            Permission::create(['name' => 'messages.send', 'guard_name' => 'web']),
        ];

        // Permissions Administration
        $permissions['admin'] = [
            Permission::create(['name' => 'users.manage', 'guard_name' => 'web']),
            Permission::create(['name' => 'settings.manage', 'guard_name' => 'web']),
            Permission::create(['name' => 'audit.view', 'guard_name' => 'web']),
        ];

        return $permissions;
    }

    /**
     * Créer les rôles et attribuer les permissions
     */
    private function createRolesWithPermissions(array $permissions): void
    {
        // 1. SUPER ADMIN - Accès total
        $superAdmin = Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo(Permission::all());

        // 2. ADMIN FONCTIONNEL - Gestion administrative complète
        $adminFonctionnel = Role::create(['name' => 'admin_fonctionnel', 'guard_name' => 'web']);
        $adminFonctionnel->givePermissionTo([
            // Planning complet
            ...$permissions['planning'],
            // Personnel complet
            ...$permissions['personnel'],
            // Transmissions lecture seule
            $permissions['transmissions'][0], // view
            // Gardes lecture + gestion
            ...$permissions['gardes'],
            // Documents complet
            ...$permissions['documents'],
            // Messages
            ...$permissions['messages'],
            // Administration
            ...$permissions['admin'],
        ]);

        // 3. CADRE DE SANTÉ - Gestion des équipes et plannings
        $cadreSante = Role::create(['name' => 'cadre_sante', 'guard_name' => 'web']);
        $cadreSante->givePermissionTo([
            // Planning complet
            ...$permissions['planning'],
            // Personnel complet
            ...$permissions['personnel'],
            // Transmissions lecture seule
            $permissions['transmissions'][0], // view
            // Gardes complet
            ...$permissions['gardes'],
            // Documents lecture seule
            $permissions['documents'][0], // view
            // Messages
            ...$permissions['messages'],
            // Audit
            $permissions['admin'][2], // audit.view
        ]);

        // 4. MÉDECIN - Accès médical complet
        $medecin = Role::create(['name' => 'medecin', 'guard_name' => 'web']);
        $medecin->givePermissionTo([
            // Planning complet
            ...$permissions['planning'],
            // Personnel lecture seule
            $permissions['personnel'][0], // view
            // Transmissions complet
            ...$permissions['transmissions'],
            // Gardes lecture seule
            $permissions['gardes'][0], // view
            // Documents lecture seule
            $permissions['documents'][0], // view
            // Messages
            ...$permissions['messages'],
        ]);

        // 5. INFIRMIER - Gestion opérationnelle quotidienne
        $infirmier = Role::create(['name' => 'infirmier', 'guard_name' => 'web']);
        $infirmier->givePermissionTo([
            // Planning complet
            ...$permissions['planning'],
            // Personnel lecture seule
            $permissions['personnel'][0], // view
            // Transmissions complet
            ...$permissions['transmissions'],
            // Gardes lecture seule
            $permissions['gardes'][0], // view
            // Documents lecture + upload
            $permissions['documents'][0], // view
            $permissions['documents'][1], // upload
            // Messages
            ...$permissions['messages'],
        ]);

        // 6. AIDE-SOIGNANT - Consultation et contribution limitée
        $aideSoignant = Role::create(['name' => 'aide_soignant', 'guard_name' => 'web']);
        $aideSoignant->givePermissionTo([
            // Planning lecture seule
            $permissions['planning'][0], // view
            // Personnel lecture seule
            $permissions['personnel'][0], // view
            // Transmissions lecture seule
            $permissions['transmissions'][0], // view
            // Gardes lecture seule
            $permissions['gardes'][0], // view
            // Documents lecture seule
            $permissions['documents'][0], // view
            // Messages
            ...$permissions['messages'],
        ]);

        // 7. SECRÉTARIAT - Gestion administrative
        $secretariat = Role::create(['name' => 'secretariat', 'guard_name' => 'web']);
        $secretariat->givePermissionTo([
            // Planning complet
            ...$permissions['planning'],
            // Personnel complet
            ...$permissions['personnel'],
            // Pas de transmissions
            // Gardes lecture seule
            $permissions['gardes'][0], // view
            // Documents lecture + upload
            $permissions['documents'][0], // view
            $permissions['documents'][1], // upload
            // Messages
            ...$permissions['messages'],
        ]);

        // 8. TECHNICIEN - Accès technique
        $technicien = Role::create(['name' => 'technicien', 'guard_name' => 'web']);
        $technicien->givePermissionTo([
            // Planning lecture seule
            $permissions['planning'][0], // view
            // Personnel lecture seule
            $permissions['personnel'][0], // view
            // Pas de transmissions
            // Gardes lecture seule
            $permissions['gardes'][0], // view
            // Documents lecture seule
            $permissions['documents'][0], // view
            // Messages
            ...$permissions['messages'],
        ]);

        $this->command->info('✅ Rôles et permissions créés avec succès');
        $this->command->info('📊 Total : 8 rôles et ' . Permission::count() . ' permissions');
    }
}