<?php

namespace Database\Seeders;

use App\Models\Salle;
use Illuminate\Database\Seeder;

/**
 * Seeder pour les salles de dialyse
 * 
 * Crée 5 salles réalistes pour les tests
 */
class SalleSeeder extends Seeder
{
    /**
     * Exécuter le seeder
     */
    public function run(): void
    {
        $salles = [
            [
                'name' => 'Salle Hémodialyse 1',
                'code' => 'HD-01',
                'floor' => '2',
                'building' => 'A',
                'capacity' => 6,
                'is_isolation' => false,
                'equipment' => [
                    'Moniteur cardiaque',
                    'Défibrillateur',
                    'Dialyseur x6',
                    'Pompe à perfusion',
                    'Oxygénothérapie',
                ],
                'is_active' => true,
                'notes' => 'Salle principale pour hémodialyse standard',
            ],
            [
                'name' => 'Salle Hémodialyse 2',
                'code' => 'HD-02',
                'floor' => '2',
                'building' => 'A',
                'capacity' => 6,
                'is_isolation' => false,
                'equipment' => [
                    'Moniteur cardiaque',
                    'Défibrillateur',
                    'Dialyseur x6',
                    'Pompe à perfusion',
                ],
                'is_active' => true,
                'notes' => 'Salle secondaire pour hémodialyse',
            ],
            [
                'name' => 'Salle Isolement',
                'code' => 'ISO-01',
                'floor' => '2',
                'building' => 'A',
                'capacity' => 2,
                'is_isolation' => true,
                'equipment' => [
                    'Moniteur cardiaque',
                    'Défibrillateur',
                    'Dialyseur x2',
                    'Pompe à perfusion',
                    'Système de ventilation HEPA',
                    'Équipement de protection',
                ],
                'is_active' => true,
                'notes' => 'Salle d\'isolement pour patients infectieux',
            ],
            [
                'name' => 'Salle Hémodiafiltration',
                'code' => 'HDF-01',
                'floor' => '3',
                'building' => 'A',
                'capacity' => 4,
                'is_isolation' => false,
                'equipment' => [
                    'Moniteur cardiaque',
                    'Défibrillateur',
                    'Dialyseur HDF x4',
                    'Pompe à perfusion',
                    'Système de préparation du liquide',
                ],
                'is_active' => true,
                'notes' => 'Salle spécialisée en hémodiafiltration',
            ],
            [
                'name' => 'Salle Dialyse Péritonéale',
                'code' => 'DP-01',
                'floor' => '1',
                'building' => 'B',
                'capacity' => 3,
                'is_isolation' => false,
                'equipment' => [
                    'Cycleur automatisé',
                    'Balance de précision',
                    'Matériel stérile',
                    'Poches de dialysat',
                ],
                'is_active' => true,
                'notes' => 'Salle pour dialyse péritonéale et formation',
            ],
        ];

        foreach ($salles as $salleData) {
            Salle::create($salleData);
        }

        $this->command->info('✅ 5 salles de dialyse créées avec succès');
    }
}