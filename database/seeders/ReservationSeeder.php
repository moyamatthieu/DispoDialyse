<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\Salle;
use App\Models\Personnel;
use App\Models\User;
use App\Enums\StatutReservationEnum;
use App\Enums\TypeDialyseEnum;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Seeder pour les réservations de test
 * 
 * Crée 50 réservations réalistes sur 2 semaines
 */
class ReservationSeeder extends Seeder
{
    /**
     * Exécuter le seeder
     */
    public function run(): void
    {
        $salles = Salle::all();
        $personnel = Personnel::active()->get();
        $user = User::first();

        if ($salles->isEmpty() || $personnel->isEmpty() || !$user) {
            $this->command->error('❌ Veuillez d\'abord exécuter les seeders pour Salles, Personnel et Users');
            return;
        }

        $startDate = now()->startOfWeek();
        $reservationsCreated = 0;

        // Créer des réservations pour les 2 prochaines semaines
        for ($day = 0; $day < 14; $day++) {
            $currentDate = $startDate->copy()->addDays($day);
            
            // Ignorer les dimanches
            if ($currentDate->isSunday()) {
                continue;
            }

            // 3-5 réservations par jour
            $reservationsPerDay = rand(3, 5);

            for ($i = 0; $i < $reservationsPerDay; $i++) {
                $salle = $salles->random();
                
                // Heures de début possibles: 8h, 10h, 12h, 14h, 16h
                $startHours = [8, 10, 12, 14, 16];
                $startHour = $startHours[array_rand($startHours)];
                
                $startTime = $currentDate->copy()->setTime($startHour, 0);
                
                // Durée selon le type de dialyse
                $typesDialyse = [
                    TypeDialyseEnum::HEMODIALYSIS,
                    TypeDialyseEnum::HEMODIAFILTRATION,
                    TypeDialyseEnum::PERITONEAL_DIALYSIS,
                ];
                
                $typeDialyse = $typesDialyse[array_rand($typesDialyse)];
                
                $duration = match($typeDialyse) {
                    TypeDialyseEnum::HEMODIALYSIS => rand(180, 240),
                    TypeDialyseEnum::HEMODIAFILTRATION => rand(180, 240),
                    TypeDialyseEnum::PERITONEAL_DIALYSIS => rand(60, 90),
                    default => 180,
                };
                
                $endTime = $startTime->copy()->addMinutes($duration);
                
                // Vérifier qu'on ne dépasse pas 20h
                if ($endTime->hour >= 20) {
                    continue;
                }

                // Vérifier disponibilité de la salle
                $conflict = Reservation::where('salle_id', $salle->id)
                    ->where(function ($q) use ($startTime, $endTime) {
                        $q->whereBetween('start_time', [$startTime, $endTime])
                          ->orWhereBetween('end_time', [$startTime, $endTime])
                          ->orWhere(function ($q2) use ($startTime, $endTime) {
                              $q2->where('start_time', '<=', $startTime)
                                 ->where('end_time', '>=', $endTime);
                          });
                    })
                    ->exists();

                if ($conflict) {
                    continue;
                }

                // Statut selon la date
                $status = $this->determineStatus($startTime);

                // Créer la réservation
                $reservation = Reservation::create([
                    'salle_id' => $salle->id,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'patient_reference' => 'PAT-' . now()->year . '-' . str_pad($reservationsCreated + 1, 3, '0', STR_PAD_LEFT),
                    'patient_initials' => $this->generateInitials(),
                    'dialysis_type' => $typeDialyse,
                    'status' => $status,
                    'notes' => $this->generateNotes(),
                    'special_requirements' => $this->generateSpecialRequirements($salle),
                    'created_by' => $user->id,
                ]);

                // Attacher 1-3 membres du personnel
                $personnelCount = rand(1, 3);
                $selectedPersonnel = $personnel->random(min($personnelCount, $personnel->count()));
                $reservation->personnel()->attach($selectedPersonnel->pluck('id'));

                $reservationsCreated++;
            }
        }

        $this->command->info("✅ {$reservationsCreated} réservations de test créées avec succès");
    }

    /**
     * Déterminer le statut selon la date
     */
    private function determineStatus(Carbon $startTime): StatutReservationEnum
    {
        $now = now();

        if ($startTime->lt($now->copy()->subDay())) {
            // Passé : principalement completed
            return rand(1, 10) > 2 
                ? StatutReservationEnum::COMPLETED 
                : StatutReservationEnum::CANCELLED;
        }

        if ($startTime->isToday() && $startTime->lt($now)) {
            // Aujourd'hui mais déjà commencé
            return rand(1, 10) > 7 
                ? StatutReservationEnum::IN_PROGRESS 
                : StatutReservationEnum::COMPLETED;
        }

        // Future: scheduled
        return StatutReservationEnum::SCHEDULED;
    }

    /**
     * Générer des initiales aléatoires
     */
    private function generateInitials(): string
    {
        $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return $letters[rand(0, 25)] . '.' . $letters[rand(0, 25)] . '.';
    }

    /**
     * Générer des notes aléatoires
     */
    private function generateNotes(): ?string
    {
        $notes = [
            null,
            'Surveiller tension artérielle',
            'Patient sous anticoagulants',
            'Première séance - surveillance rapprochée',
            'Antécédents cardiaques - monitoring continu',
            'Allergie à l\'héparine - utiliser alternative',
            'Accès fistule gauche',
            'Patient diabétique',
        ];

        return $notes[array_rand($notes)];
    }

    /**
     * Générer des besoins spéciaux
     */
    private function generateSpecialRequirements(Salle $salle): ?string
    {
        if ($salle->is_isolation && rand(1, 10) > 7) {
            return 'Isolement requis | Précautions contact';
        }

        $requirements = [
            null,
            null,
            null, // Plus de chances d'avoir null
            'Fauteuil roulant',
            'Oxygénothérapie continue',
            'Assistance pour mobilité',
        ];

        return $requirements[array_rand($requirements)];
    }
}