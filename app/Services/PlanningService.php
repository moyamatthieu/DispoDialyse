<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Salle;
use App\Models\Personnel;
use App\Enums\StatutReservationEnum;
use App\Enums\TypeDialyseEnum;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service de gestion du planning des salles de dialyse
 * 
 * Centralise toute la logique métier complexe :
 * - Détection de conflits
 * - Suggestions d'alternatives
 * - Calcul de statistiques
 * - Gestion des récurrences
 */
class PlanningService
{
    /**
     * Durées min/max par type de dialyse (en minutes)
     */
    private const DURATIONS = [
        'hemodialyse' => ['min' => 180, 'max' => 300],
        'hemodiafiltration' => ['min' => 180, 'max' => 300],
        'dialyse_peritoneale' => ['min' => 30, 'max' => 120],
    ];

    /**
     * Détecter tous les conflits pour une réservation potentielle
     * 
     * @param array $params Paramètres de la réservation
     * @return array Liste des conflits détectés
     */
    public function detectConflits(array $params): array
    {
        $conflicts = [];
        
        $salleId = $params['salle_id'];
        $dateDebut = Carbon::parse($params['date_debut']);
        $dateFin = Carbon::parse($params['date_fin']);
        $personnelIds = $params['personnel_ids'] ?? [];
        $excludeReservationId = $params['exclude_reservation_id'] ?? null;

        // Vérifier disponibilité de la salle
        if (!$this->isSalleAvailable($salleId, $dateDebut, $dateFin, $excludeReservationId)) {
            $conflictingReservation = $this->getConflictingReservation($salleId, $dateDebut, $dateFin, $excludeReservationId);
            $conflicts[] = [
                'type' => 'salle_occupee',
                'message' => "La salle est déjà réservée de {$conflictingReservation->start_time->format('H:i')} à {$conflictingReservation->end_time->format('H:i')}",
                'severity' => 'error',
                'reservation_id' => $conflictingReservation->id,
            ];
        }

        // Vérifier disponibilité du personnel
        foreach ($personnelIds as $personnelId) {
            if (!$this->isPersonnelAvailable($personnelId, $dateDebut, $dateFin, $excludeReservationId)) {
                $personnel = Personnel::find($personnelId);
                $conflictingReservation = $this->getPersonnelConflictingReservation($personnelId, $dateDebut, $dateFin, $excludeReservationId);
                $conflicts[] = [
                    'type' => 'personnel_indisponible',
                    'message' => "{$personnel->full_name} est déjà assigné(e) à une autre séance",
                    'severity' => 'error',
                    'personnel_id' => $personnelId,
                    'reservation_id' => $conflictingReservation->id,
                ];
            }
        }

        // Vérifier durée selon type de dialyse
        $typeDialyse = $params['type_dialyse'] ?? 'hemodialyse';
        $duration = $dateDebut->diffInMinutes($dateFin);
        
        if (isset(self::DURATIONS[$typeDialyse])) {
            $minDuration = self::DURATIONS[$typeDialyse]['min'];
            $maxDuration = self::DURATIONS[$typeDialyse]['max'];
            
            if ($duration < $minDuration) {
                $conflicts[] = [
                    'type' => 'duree_insuffisante',
                    'message' => "Durée trop courte pour une {$typeDialyse}. Minimum : {$minDuration} minutes",
                    'severity' => 'warning',
                ];
            }
            
            if ($duration > $maxDuration) {
                $conflicts[] = [
                    'type' => 'duree_excessive',
                    'message' => "Durée trop longue pour une {$typeDialyse}. Maximum : {$maxDuration} minutes",
                    'severity' => 'warning',
                ];
            }
        }

        // Vérifier si isolement requis et salle compatible
        if (isset($params['isolement_requis']) && $params['isolement_requis']) {
            $salle = Salle::find($salleId);
            if (!$salle->is_isolation) {
                $conflicts[] = [
                    'type' => 'isolement_non_disponible',
                    'message' => "Cette salle n'est pas équipée pour l'isolement",
                    'severity' => 'error',
                ];
            }
        }

        // Vérifier nombre minimal de personnel (au moins 1 IDE)
        if (empty($personnelIds)) {
            $conflicts[] = [
                'type' => 'personnel_manquant',
                'message' => "Au moins un membre du personnel doit être assigné",
                'severity' => 'error',
            ];
        }

        return $conflicts;
    }

    /**
     * Suggérer des créneaux alternatifs disponibles
     * 
     * @param array $params Paramètres de recherche
     * @return array Créneaux disponibles
     */
    public function suggestAlternatives(array $params): array
    {
        $alternatives = [];
        
        $salleId = $params['salle_id'];
        $dateDebut = Carbon::parse($params['date_debut']);
        $dateFin = Carbon::parse($params['date_fin']);
        $duration = $dateDebut->diffInMinutes($dateFin);

        // Chercher créneaux le même jour dans la même salle
        $sameDaySlots = $this->findAvailableSlots($salleId, $dateDebut->copy()->startOfDay(), $dateDebut->copy()->endOfDay(), $duration);
        
        foreach ($sameDaySlots as $slot) {
            $alternatives[] = [
                'type' => 'meme_jour_meme_salle',
                'salle_id' => $salleId,
                'date_debut' => $slot['start'],
                'date_fin' => $slot['end'],
                'label' => "Même jour à {$slot['start']->format('H:i')} - {$slot['end']->format('H:i')}",
                'priority' => 1,
            ];
        }

        // Chercher dans d'autres salles actives le même jour
        $otherSalles = Salle::active()
            ->where('id', '!=', $salleId)
            ->get();

        foreach ($otherSalles as $salle) {
            if ($this->isSalleAvailable($salle->id, $dateDebut, $dateFin)) {
                $alternatives[] = [
                    'type' => 'meme_creneau_autre_salle',
                    'salle_id' => $salle->id,
                    'salle_nom' => $salle->name,
                    'date_debut' => $dateDebut,
                    'date_fin' => $dateFin,
                    'label' => "Même créneau en {$salle->name}",
                    'priority' => 2,
                ];
            }
        }

        // Chercher créneaux les jours suivants (3 jours)
        for ($i = 1; $i <= 3; $i++) {
            $nextDay = $dateDebut->copy()->addDays($i);
            if ($this->isSalleAvailable($salleId, $nextDay, $nextDay->copy()->addMinutes($duration))) {
                $alternatives[] = [
                    'type' => 'jour_suivant',
                    'salle_id' => $salleId,
                    'date_debut' => $nextDay,
                    'date_fin' => $nextDay->copy()->addMinutes($duration),
                    'label' => "{$nextDay->format('d/m/Y')} à {$nextDay->format('H:i')}",
                    'priority' => 3,
                ];
            }
        }

        // Trier par priorité
        usort($alternatives, fn($a, $b) => $a['priority'] <=> $b['priority']);

        return array_slice($alternatives, 0, 5); // Limiter à 5 suggestions
    }

    /**
     * Trouver les créneaux disponibles dans une salle pour une journée
     * 
     * @param int $salleId ID de la salle
     * @param Carbon $startOfDay Début de la journée
     * @param Carbon $endOfDay Fin de la journée
     * @param int $duration Durée souhaitée en minutes
     * @return array Créneaux disponibles
     */
    private function findAvailableSlots(int $salleId, Carbon $startOfDay, Carbon $endOfDay, int $duration): array
    {
        $slots = [];
        
        // Récupérer toutes les réservations de la journée
        $reservations = Reservation::where('salle_id', $salleId)
            ->whereBetween('start_time', [$startOfDay, $endOfDay])
            ->whereNotIn('status', [StatutReservationEnum::CANCELLED, StatutReservationEnum::NO_SHOW])
            ->orderBy('start_time')
            ->get();

        // Heures d'ouverture (8h - 20h)
        $workStart = $startOfDay->copy()->setTime(8, 0);
        $workEnd = $startOfDay->copy()->setTime(20, 0);

        $currentTime = $workStart->copy();

        foreach ($reservations as $reservation) {
            $gapDuration = $currentTime->diffInMinutes($reservation->start_time);
            
            if ($gapDuration >= $duration) {
                $slots[] = [
                    'start' => $currentTime->copy(),
                    'end' => $currentTime->copy()->addMinutes($duration),
                ];
            }
            
            $currentTime = $reservation->end_time->copy();
        }

        // Vérifier le créneau final après la dernière réservation
        if ($currentTime->lt($workEnd)) {
            $finalGap = $currentTime->diffInMinutes($workEnd);
            if ($finalGap >= $duration) {
                $slots[] = [
                    'start' => $currentTime->copy(),
                    'end' => $currentTime->copy()->addMinutes($duration),
                ];
            }
        }

        return $slots;
    }

    /**
     * Calculer les statistiques d'une salle sur une période
     * 
     * @param Salle $salle Salle concernée
     * @param Carbon $debut Date de début
     * @param Carbon $fin Date de fin
     * @return array Statistiques
     */
    public function getStatistiquesSalle(Salle $salle, Carbon $debut, Carbon $fin): array
    {
        $reservations = $salle->reservations()
            ->betweenDates($debut, $fin)
            ->whereNotIn('status', [StatutReservationEnum::CANCELLED, StatutReservationEnum::NO_SHOW])
            ->get();

        $totalReservations = $reservations->count();
        $totalHeures = $reservations->sum(fn($r) => $r->duration_in_minutes) / 60;
        
        $tauxOccupation = $this->getTauxOccupation($salle, $debut, $fin);

        $parType = $reservations->groupBy('dialysis_type')->map(fn($group) => $group->count());

        return [
            'total_reservations' => $totalReservations,
            'total_heures' => round($totalHeures, 2),
            'taux_occupation' => round($tauxOccupation, 2),
            'par_type_dialyse' => $parType->toArray(),
            'duree_moyenne' => $totalReservations > 0 ? round($totalHeures / $totalReservations, 2) : 0,
            'periode' => [
                'debut' => $debut->format('d/m/Y'),
                'fin' => $fin->format('d/m/Y'),
            ],
        ];
    }

    /**
     * Calculer le taux d'occupation d'une salle sur une période
     * 
     * @param Salle $salle Salle concernée
     * @param Carbon $debut Date de début
     * @param Carbon $fin Date de fin
     * @return float Taux en pourcentage
     */
    public function getTauxOccupation(Salle $salle, Carbon $debut, Carbon $fin): float
    {
        $nombreJours = $debut->diffInDays($fin) + 1;
        $heuresOuverture = 12; // 8h-20h = 12h par jour
        $totalHeuresDisponibles = $nombreJours * $heuresOuverture;

        $heuresReservees = $salle->reservations()
            ->betweenDates($debut, $fin)
            ->whereNotIn('status', [StatutReservationEnum::CANCELLED, StatutReservationEnum::NO_SHOW])
            ->get()
            ->sum(fn($r) => $r->duration_in_minutes / 60);

        return $totalHeuresDisponibles > 0 ? ($heuresReservees / $totalHeuresDisponibles) * 100 : 0;
    }

    /**
     * Créer une série de réservations récurrentes
     * 
     * @param array $params Paramètres de la série
     * @return Collection Collection des réservations créées
     */
    public function createRecurringReservations(array $params): Collection
    {
        $reservations = collect();
        
        $dateDebut = Carbon::parse($params['date_debut']);
        $dateFin = Carbon::parse($params['date_fin']);
        $recurrenceType = $params['recurrence_type']; // 'daily', 'weekly', 'biweekly'
        $occurrences = $params['occurrences'] ?? 10;
        
        $duration = $dateDebut->diffInMinutes($dateFin);

        for ($i = 0; $i < $occurrences; $i++) {
            $currentStart = match($recurrenceType) {
                'daily' => $dateDebut->copy()->addDays($i),
                'weekly' => $dateDebut->copy()->addWeeks($i),
                'biweekly' => $dateDebut->copy()->addWeeks($i * 2),
                default => $dateDebut->copy()->addWeeks($i),
            };

            $currentEnd = $currentStart->copy()->addMinutes($duration);

            // Vérifier disponibilité avant de créer
            if ($this->isSalleAvailable($params['salle_id'], $currentStart, $currentEnd)) {
                $reservation = Reservation::create([
                    'salle_id' => $params['salle_id'],
                    'start_time' => $currentStart,
                    'end_time' => $currentEnd,
                    'patient_reference' => $params['patient_reference'],
                    'patient_initials' => $params['patient_initials'] ?? null,
                    'dialysis_type' => $params['type_dialyse'],
                    'status' => StatutReservationEnum::SCHEDULED,
                    'notes' => $params['notes'] ?? null,
                    'special_requirements' => $params['special_requirements'] ?? null,
                    'created_by' => auth()->id(),
                ]);

                // Attacher le personnel
                if (!empty($params['personnel_ids'])) {
                    $reservation->personnel()->attach($params['personnel_ids']);
                }

                $reservations->push($reservation);
            }
        }

        return $reservations;
    }

    /**
     * Vérifier si une salle est disponible
     * 
     * @param int $salleId ID de la salle
     * @param Carbon $dateDebut Date de début
     * @param Carbon $dateFin Date de fin
     * @param int|null $excludeReservationId ID de réservation à exclure
     * @return bool
     */
    public function isSalleAvailable(int $salleId, Carbon $dateDebut, Carbon $dateFin, ?int $excludeReservationId = null): bool
    {
        $query = Reservation::where('salle_id', $salleId)
            ->where(function ($q) use ($dateDebut, $dateFin) {
                $q->whereBetween('start_time', [$dateDebut, $dateFin])
                  ->orWhereBetween('end_time', [$dateDebut, $dateFin])
                  ->orWhere(function ($q2) use ($dateDebut, $dateFin) {
                      $q2->where('start_time', '<=', $dateDebut)
                         ->where('end_time', '>=', $dateFin);
                  });
            })
            ->whereNotIn('status', [StatutReservationEnum::CANCELLED, StatutReservationEnum::NO_SHOW]);

        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }

        return !$query->exists();
    }

    /**
     * Vérifier si un membre du personnel est disponible
     * 
     * @param int $personnelId ID du personnel
     * @param Carbon $dateDebut Date de début
     * @param Carbon $dateFin Date de fin
     * @param int|null $excludeReservationId ID de réservation à exclure
     * @return bool
     */
    public function isPersonnelAvailable(int $personnelId, Carbon $dateDebut, Carbon $dateFin, ?int $excludeReservationId = null): bool
    {
        $query = Reservation::whereHas('personnel', function ($q) use ($personnelId) {
                $q->where('personnel.id', $personnelId);
            })
            ->where(function ($q) use ($dateDebut, $dateFin) {
                $q->whereBetween('start_time', [$dateDebut, $dateFin])
                  ->orWhereBetween('end_time', [$dateDebut, $dateFin])
                  ->orWhere(function ($q2) use ($dateDebut, $dateFin) {
                      $q2->where('start_time', '<=', $dateDebut)
                         ->where('end_time', '>=', $dateFin);
                  });
            })
            ->whereNotIn('status', [StatutReservationEnum::CANCELLED, StatutReservationEnum::NO_SHOW]);

        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }

        return !$query->exists();
    }

    /**
     * Vérifier si une réservation peut être annulée
     * 
     * @param Reservation $reservation Réservation concernée
     * @param \App\Models\User $user Utilisateur demandeur
     * @return bool
     */
    public function canCancelReservation(Reservation $reservation, $user): bool
    {
        // Vérifier le statut
        if (!$reservation->isCancellable()) {
            return false;
        }

        // Vérifier les permissions
        if ($user->can('planning.delete')) {
            return true;
        }

        // Le créateur peut annuler sa propre réservation
        if ($reservation->created_by === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Obtenir la réservation en conflit pour une salle
     * 
     * @param int $salleId ID de la salle
     * @param Carbon $dateDebut Date de début
     * @param Carbon $dateFin Date de fin
     * @param int|null $excludeReservationId ID de réservation à exclure
     * @return Reservation|null
     */
    private function getConflictingReservation(int $salleId, Carbon $dateDebut, Carbon $dateFin, ?int $excludeReservationId = null): ?Reservation
    {
        $query = Reservation::where('salle_id', $salleId)
            ->where(function ($q) use ($dateDebut, $dateFin) {
                $q->whereBetween('start_time', [$dateDebut, $dateFin])
                  ->orWhereBetween('end_time', [$dateDebut, $dateFin])
                  ->orWhere(function ($q2) use ($dateDebut, $dateFin) {
                      $q2->where('start_time', '<=', $dateDebut)
                         ->where('end_time', '>=', $dateFin);
                  });
            })
            ->whereNotIn('status', [StatutReservationEnum::CANCELLED, StatutReservationEnum::NO_SHOW]);

        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }

        return $query->first();
    }

    /**
     * Obtenir la réservation en conflit pour un personnel
     * 
     * @param int $personnelId ID du personnel
     * @param Carbon $dateDebut Date de début
     * @param Carbon $dateFin Date de fin
     * @param int|null $excludeReservationId ID de réservation à exclure
     * @return Reservation|null
     */
    private function getPersonnelConflictingReservation(int $personnelId, Carbon $dateDebut, Carbon $dateFin, ?int $excludeReservationId = null): ?Reservation
    {
        $query = Reservation::whereHas('personnel', function ($q) use ($personnelId) {
                $q->where('personnel.id', $personnelId);
            })
            ->where(function ($q) use ($dateDebut, $dateFin) {
                $q->whereBetween('start_time', [$dateDebut, $dateFin])
                  ->orWhereBetween('end_time', [$dateDebut, $dateFin])
                  ->orWhere(function ($q2) use ($dateDebut, $dateFin) {
                      $q2->where('start_time', '<=', $dateDebut)
                         ->where('end_time', '>=', $dateFin);
                  });
            })
            ->whereNotIn('status', [StatutReservationEnum::CANCELLED, StatutReservationEnum::NO_SHOW]);

        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }

        return $query->first();
    }
}