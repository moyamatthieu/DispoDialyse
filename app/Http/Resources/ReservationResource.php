<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource pour transformer les données de réservation en JSON
 * 
 * Fournit un format cohérent pour les réponses API
 */
class ReservationResource extends JsonResource
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
            
            // Informations patient (anonymisées)
            'patient' => [
                'reference' => $this->patient_reference,
                'initials' => $this->patient_initials,
            ],
            
            // Salle
            'salle' => [
                'id' => $this->salle_id,
                'nom' => $this->salle->name,
                'code' => $this->salle->code,
                'batiment' => $this->salle->building,
                'etage' => $this->salle->floor,
                'capacite' => $this->salle->capacity,
                'isolement' => $this->salle->is_isolation,
            ],
            
            // Dates et horaires
            'planning' => [
                'date_debut' => $this->start_time->toIso8601String(),
                'date_fin' => $this->end_time->toIso8601String(),
                'date_debut_format' => $this->start_time->format('d/m/Y H:i'),
                'date_fin_format' => $this->end_time->format('d/m/Y H:i'),
                'jour' => $this->start_time->locale('fr')->isoFormat('dddd D MMMM YYYY'),
                'heure_debut' => $this->start_time->format('H:i'),
                'heure_fin' => $this->end_time->format('H:i'),
                'duree_minutes' => $this->duration_in_minutes,
                'duree_format' => $this->duration_formatted,
            ],
            
            // Type et statut
            'type_dialyse' => [
                'code' => $this->dialysis_type->value,
                'label' => $this->dialysis_type->label(),
                'couleur' => $this->getColorByType(),
            ],
            
            'statut' => [
                'code' => $this->status->value,
                'label' => $this->status->label(),
                'badge_class' => $this->getBadgeClass(),
            ],
            
            // Personnel assigné
            'personnel' => $this->personnel->map(function ($person) {
                return [
                    'id' => $person->id,
                    'nom_complet' => $person->full_name,
                    'prenom' => $person->first_name,
                    'nom' => $person->last_name,
                    'fonction' => $person->job_title,
                    'role_session' => $person->pivot->role_in_session ?? null,
                ];
            }),
            
            // Informations complémentaires
            'notes' => $this->notes,
            'besoins_speciaux' => $this->special_requirements,
            
            // État de la réservation
            'etats' => [
                'est_aujourdhui' => $this->isToday(),
                'est_en_cours' => $this->isInProgress(),
                'est_modifiable' => $this->isEditable(),
                'est_annulable' => $this->isCancellable(),
            ],
            
            // Annulation (si applicable)
            'annulation' => $this->when($this->cancelled_at, [
                'date' => $this->cancelled_at?->toIso8601String(),
                'motif' => $this->cancellation_reason,
            ]),
            
            // Créateur
            'createur' => $this->when($this->creator, [
                'id' => $this->creator?->id,
                'nom' => $this->creator?->name,
                'email' => $this->creator?->email,
            ]),
            
            // Transmissions associées
            'nombre_transmissions' => $this->whenLoaded('transmissions', 
                fn() => $this->transmissions->count()
            ),
            
            // Timestamps
            'cree_le' => $this->created_at->toIso8601String(),
            'modifie_le' => $this->updated_at->toIso8601String(),
            'cree_le_format' => $this->created_at->format('d/m/Y H:i'),
            'modifie_le_format' => $this->updated_at->format('d/m/Y H:i'),
            
            // URLs
            'urls' => [
                'show' => route('planning.show', $this->id),
                'edit' => $this->isEditable() ? route('planning.edit', $this->id) : null,
                'cancel' => $this->isCancellable() ? route('planning.cancel', $this->id) : null,
            ],
        ];
    }

    /**
     * Obtenir la couleur selon le type de dialyse
     */
    private function getColorByType(): string
    {
        return match($this->dialysis_type->value) {
            'hemodialysis' => '#3b82f6',
            'hemodiafiltration' => '#8b5cf6',
            'peritoneal_dialysis' => '#10b981',
            default => '#6b7280',
        };
    }

    /**
     * Obtenir la classe CSS pour le badge de statut
     */
    private function getBadgeClass(): string
    {
        return match($this->status->value) {
            'scheduled' => 'bg-blue-100 text-blue-800',
            'in_progress' => 'bg-green-100 text-green-800',
            'completed' => 'bg-gray-100 text-gray-800',
            'cancelled' => 'bg-red-100 text-red-800',
            'no_show' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}