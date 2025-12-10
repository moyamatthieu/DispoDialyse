<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource API pour Personnel (vue détaillée)
 */
class PersonnelDetailResource extends JsonResource
{
    /**
     * Transformer la ressource en tableau
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            
            // Identité
            'nom_complet' => $this->full_name,
            'prenom' => $this->first_name,
            'nom' => $this->last_name,
            'initiales' => $this->initials,
            'photo_url' => $this->photo_url ?? '/images/default-avatar.png',
            
            // Informations professionnelles
            'fonction' => $this->job_title,
            'specialite' => $this->specialty,
            'service' => $this->department,
            'type_contrat' => $this->employment_type,
            'type_contrat_label' => $this->getEmploymentTypeLabel(),
            
            // Contact
            'email' => $this->email_pro,
            'telephone_fixe' => $this->phone_office,
            'telephone_mobile' => $this->phone_mobile,
            'telephone_bip' => $this->phone_pager,
            'telephone_principal' => $this->primary_phone,
            'extension' => $this->extension,
            
            // Compétences
            'qualifications' => $this->qualifications ?? [],
            'certifications' => $this->certifications ?? [],
            'langues_parlees' => $this->languages ?? [],
            
            // Statuts
            'est_actif' => $this->is_active,
            'est_de_garde' => $this->isOnCall(),
            'a_compte_utilisateur' => $this->hasUserAccount(),
            
            // Dates
            'date_embauche' => $this->hire_date?->format('Y-m-d'),
            'date_embauche_format' => $this->hire_date?->format('d/m/Y'),
            'anciennete' => $this->hire_date?->diffForHumans(null, true),
            'date_depart' => $this->leave_date?->format('Y-m-d'),
            
            // Compte utilisateur lié
            'utilisateur' => $this->when($this->user, function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            
            // Gardes récentes
            'gardes_recentes' => $this->whenLoaded('gardes', function () {
                return $this->gardes->map(function ($garde) {
                    return [
                        'id' => $garde->id,
                        'type' => $garde->shift_type,
                        'debut' => $garde->start_datetime->toIso8601String(),
                        'fin' => $garde->end_datetime->toIso8601String(),
                        'statut' => $garde->status,
                    ];
                });
            }),
            
            // Statistiques
            'statistiques' => [
                'gardes_mois_en_cours' => $this->gardes()
                    ->where('start_datetime', '>=', now()->startOfMonth())
                    ->where('start_datetime', '<=', now()->endOfMonth())
                    ->count(),
            ],
            
            // Métadonnées
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
            
            // URLs
            'urls' => [
                'show' => route('annuaire.show', $this->id),
                'edit' => route('annuaire.edit', $this->id),
            ],
        ];
    }

    /**
     * Obtenir le libellé du type de contrat
     */
    private function getEmploymentTypeLabel(): string
    {
        return match($this->employment_type) {
            'full_time' => 'Temps plein',
            'part_time' => 'Temps partiel',
            'contractor' => 'Contractuel',
            default => $this->employment_type,
        };
    }
}