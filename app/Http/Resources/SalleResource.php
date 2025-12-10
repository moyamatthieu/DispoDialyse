<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource pour transformer les données de salle en JSON
 * 
 * Fournit un format cohérent pour les réponses API
 */
class SalleResource extends JsonResource
{
    /**
     * Transformer la ressource en tableau
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            
            // Informations de base
            'nom' => $this->name,
            'code' => $this->code,
            
            // Localisation
            'localisation' => [
                'batiment' => $this->building,
                'etage' => $this->floor,
                'description' => $this->building && $this->floor 
                    ? "Bâtiment {$this->building}, Étage {$this->floor}"
                    : null,
            ],
            
            // Capacité et caractéristiques
            'capacite' => $this->capacity,
            'est_isolement' => $this->is_isolation,
            'est_active' => $this->is_active,
            
            // Équipements
            'equipements' => $this->equipment ?? [],
            
            // Notes
            'notes' => $this->notes,
            
            // Statistiques (si demandées)
            'statistiques' => $this->when(
                $request->has('include_stats'),
                fn() => $this->getStatistiques()
            ),
            
            // Réservations du jour (si demandées)
            'reservations_aujourdhui' => $this->when(
                $request->has('include_today_reservations'),
                fn() => $this->todayReservations()->get()->map(function ($reservation) {
                    return [
                        'id' => $reservation->id,
                        'patient_reference' => $reservation->patient_reference,
                        'heure_debut' => $reservation->start_time->format('H:i'),
                        'heure_fin' => $reservation->end_time->format('H:i'),
                        'type_dialyse' => $reservation->dialysis_type->value,
                        'statut' => $reservation->status->value,
                    ];
                })
            ),
            
            // Timestamps
            'cree_le' => $this->created_at->toIso8601String(),
            'modifie_le' => $this->updated_at->toIso8601String(),
            'cree_le_format' => $this->created_at->format('d/m/Y H:i'),
            'modifie_le_format' => $this->updated_at->format('d/m/Y H:i'),
            
            // URLs
            'urls' => [
                'show' => route('salles.show', $this->id),
                'availability' => route('api.salles.availability', $this->id),
                'stats' => route('api.salles.stats', $this->id),
            ],
        ];
    }

    /**
     * Obtenir les statistiques de base de la salle
     */
    private function getStatistiques(): array
    {
        $now = now();
        
        // Réservations cette semaine
        $reservationsThisWeek = $this->reservations()
            ->whereBetween('start_time', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()])
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->count();

        // Réservations ce mois
        $reservationsThisMonth = $this->reservations()
            ->whereBetween('start_time', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->count();

        return [
            'reservations_semaine' => $reservationsThisWeek,
            'reservations_mois' => $reservationsThisMonth,
        ];
    }
}