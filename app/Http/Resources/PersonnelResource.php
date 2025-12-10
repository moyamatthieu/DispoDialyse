<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource API pour Personnel (vue liste)
 */
class PersonnelResource extends JsonResource
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
            'nom_complet' => $this->full_name,
            'prenom' => $this->first_name,
            'nom' => $this->last_name,
            'initiales' => $this->initials,
            'fonction' => $this->job_title,
            'specialite' => $this->specialty,
            'service' => $this->department,
            'type_contrat' => $this->employment_type,
            'type_contrat_label' => $this->getEmploymentTypeLabel(),
            'photo_url' => $this->photo_url ?? '/images/default-avatar.png',
            'email' => $this->email_pro,
            'telephone_principal' => $this->primary_phone,
            'telephone_fixe' => $this->phone_office,
            'telephone_mobile' => $this->phone_mobile,
            'extension' => $this->extension,
            'est_actif' => $this->is_active,
            'est_de_garde' => $this->isOnCall(),
            'a_compte_utilisateur' => $this->hasUserAccount(),
            'date_embauche' => $this->hire_date?->format('Y-m-d'),
            'anciennete' => $this->hire_date?->diffForHumans(null, true),
            'url' => route('annuaire.show', $this->id),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
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