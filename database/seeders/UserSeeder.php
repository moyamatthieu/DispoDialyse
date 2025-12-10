<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder pour créer les utilisateurs de test
 * 
 * Crée 10 utilisateurs avec les 8 rôles différents
 */
class UserSeeder extends Seeder
{
    /**
     * Exécuter le seeder
     */
    public function run(): void
    {
        $password = Hash::make('Password123!');

        // 1. Super Admin
        $superAdmin = User::create([
            'username' => 'admin',
            'email' => 'admin@dispodialyse.fr',
            'password' => $password,
            'first_name' => 'Admin',
            'last_name' => 'Système',
            'phone' => '01 23 45 67 89',
            'role' => RoleEnum::SUPER_ADMIN,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole('super_admin');
        $this->command->info('✅ Super Admin créé: admin@dispodialyse.fr');

        // 2. Administrateur Fonctionnel
        $adminFonctionnel = User::create([
            'username' => 'admin.fonctionnel',
            'email' => 'admin.fonctionnel@dispodialyse.fr',
            'password' => $password,
            'first_name' => 'Marie',
            'last_name' => 'Dupont',
            'phone' => '01 23 45 67 90',
            'role' => RoleEnum::ADMIN_FONCTIONNEL,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $adminFonctionnel->assignRole('admin_fonctionnel');
        $this->command->info('✅ Admin Fonctionnel créé: admin.fonctionnel@dispodialyse.fr');

        // 3. Cadre de Santé
        $cadreSante = User::create([
            'username' => 'cadre.sante',
            'email' => 'cadre@dispodialyse.fr',
            'password' => $password,
            'first_name' => 'Sophie',
            'last_name' => 'Martin',
            'phone' => '01 23 45 67 91',
            'role' => RoleEnum::CADRE_SANTE,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $cadreSante->assignRole('cadre_sante');
        $this->command->info('✅ Cadre de Santé créé: cadre@dispodialyse.fr');

        // 4. Médecin Néphrologue
        $medecin = User::create([
            'username' => 'dr.bernard',
            'email' => 'medecin@dispodialyse.fr',
            'password' => $password,
            'first_name' => 'Jean',
            'last_name' => 'Bernard',
            'phone' => '01 23 45 67 92',
            'role' => RoleEnum::MEDECIN,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $medecin->assignRole('medecin');
        $this->command->info('✅ Médecin créé: medecin@dispodialyse.fr');

        // 5. Infirmier IDE
        $infirmier = User::create([
            'username' => 'infirmier.claire',
            'email' => 'infirmier@dispodialyse.fr',
            'password' => $password,
            'first_name' => 'Claire',
            'last_name' => 'Rousseau',
            'phone' => '01 23 45 67 93',
            'role' => RoleEnum::INFIRMIER,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $infirmier->assignRole('infirmier');
        $this->command->info('✅ Infirmier créé: infirmier@dispodialyse.fr');

        // 6. Aide-Soignant
        $aideSoignant = User::create([
            'username' => 'as.thomas',
            'email' => 'aidesoignant@dispodialyse.fr',
            'password' => $password,
            'first_name' => 'Thomas',
            'last_name' => 'Petit',
            'phone' => '01 23 45 67 94',
            'role' => RoleEnum::AIDE_SOIGNANT,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $aideSoignant->assignRole('aide_soignant');
        $this->command->info('✅ Aide-Soignant créé: aidesoignant@dispodialyse.fr');

        // 7. Secrétariat
        $secretariat = User::create([
            'username' => 'secretariat.julie',
            'email' => 'secretariat@dispodialyse.fr',
            'password' => $password,
            'first_name' => 'Julie',
            'last_name' => 'Moreau',
            'phone' => '01 23 45 67 95',
            'role' => RoleEnum::SECRETARIAT,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $secretariat->assignRole('secretariat');
        $this->command->info('✅ Secrétariat créé: secretariat@dispodialyse.fr');

        // 8. Technicien Biomédical
        $technicien = User::create([
            'username' => 'tech.pierre',
            'email' => 'technicien@dispodialyse.fr',
            'password' => $password,
            'first_name' => 'Pierre',
            'last_name' => 'Durand',
            'phone' => '01 23 45 67 96',
            'role' => RoleEnum::TECHNICIEN,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $technicien->assignRole('technicien');
        $this->command->info('✅ Technicien créé: technicien@dispodialyse.fr');

        // 9. Médecin 2
        $medecin2 = User::create([
            'username' => 'dr.laurent',
            'email' => 'medecin2@dispodialyse.fr',
            'password' => $password,
            'first_name' => 'Laurent',
            'last_name' => 'Blanc',
            'phone' => '01 23 45 67 97',
            'role' => RoleEnum::MEDECIN,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $medecin2->assignRole('medecin');
        $this->command->info('✅ Médecin 2 créé: medecin2@dispodialyse.fr');

        // 10. Infirmier 2
        $infirmier2 = User::create([
            'username' => 'infirmier.alice',
            'email' => 'infirmier2@dispodialyse.fr',
            'password' => $password,
            'first_name' => 'Alice',
            'last_name' => 'Mercier',
            'phone' => '01 23 45 67 98',
            'role' => RoleEnum::INFIRMIER,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $infirmier2->assignRole('infirmier');
        $this->command->info('✅ Infirmier 2 créé: infirmier2@dispodialyse.fr');

        $this->command->info('');
        $this->command->info('📝 Tous les utilisateurs ont le même mot de passe : Password123!');
        $this->command->info('⚠️  IMPORTANT : Changez ces mots de passe en production !');
        $this->command->info('');
        $this->command->info('📊 Total : 10 utilisateurs créés avec succès');
    }
}