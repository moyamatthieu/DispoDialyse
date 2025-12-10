<?php

namespace App\Http\Requests;

use App\Enums\TypeDialyseEnum;
use App\Services\PlanningService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Requête de validation pour la création d'une réservation
 * 
 * Validation stricte de tous les paramètres avec règles métier complexes
 */
class StoreReservationRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à effectuer cette requête
     */
    public function authorize(): bool
    {
        return $this->user()->can('planning.create');
    }

    /**
     * Règles de validation
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Salle
            'salle_id' => [
                'required',
                'integer',
                'exists:salles,id',
                function ($attribute, $value, $fail) {
                    $salle = \App\Models\Salle::find($value);
                    if (!$salle || !$salle->is_active) {
                        $fail('La salle sélectionnée n\'est pas active.');
                    }
                },
            ],

            // Patient (anonymisé)
            'patient_reference' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Z0-9\-]+$/', // Format type : PAT-2024-001
            ],
            'patient_initials' => [
                'nullable',
                'string',
                'max:10',
                'regex:/^[A-Z\.]+$/', // Format type : J.D.
            ],

            // Type de dialyse
            'type_dialyse' => [
                'required',
                'string',
                Rule::enum(TypeDialyseEnum::class),
            ],

            // Dates et heures
            'date_debut' => [
                'required',
                'date',
                'after_or_equal:now',
                function ($attribute, $value, $fail) {
                    $dateDebut = \Carbon\Carbon::parse($value);
                    // Vérifier heures d'ouverture (8h-20h)
                    if ($dateDebut->hour < 8 || $dateDebut->hour >= 20) {
                        $fail('La réservation doit être entre 8h et 20h.');
                    }
                },
            ],
            'date_fin' => [
                'required',
                'date',
                'after:date_debut',
                function ($attribute, $value, $fail) {
                    $dateFin = \Carbon\Carbon::parse($value);
                    // Vérifier heures d'ouverture
                    if ($dateFin->hour > 20 || ($dateFin->hour === 20 && $dateFin->minute > 0)) {
                        $fail('La séance doit se terminer avant 20h.');
                    }
                },
            ],

            // Personnel assigné
            'personnel_ids' => [
                'required',
                'array',
                'min:1',
            ],
            'personnel_ids.*' => [
                'required',
                'integer',
                'exists:personnel,id',
                function ($attribute, $value, $fail) {
                    $personnel = \App\Models\Personnel::find($value);
                    if (!$personnel || !$personnel->is_active) {
                        $fail('Un membre du personnel sélectionné n\'est pas actif.');
                    }
                },
            ],

            // Options
            'isolement_requis' => 'boolean',
            'notes' => 'nullable|string|max:1000',
            'special_requirements' => 'nullable|string|max:500',
            
            // Équipements spéciaux
            'equipements_speciaux' => 'nullable|array',
            'equipements_speciaux.*' => 'string|max:100',
        ];
    }

    /**
     * Validation après les règles de base
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Vérifier la durée selon le type de dialyse
            $this->validateDuration($validator);
            
            // Vérifier les conflits de disponibilité
            $this->validateAvailability($validator);
            
            // Vérifier la capacité de la salle
            $this->validateCapacity($validator);
            
            // Vérifier l'isolement
            $this->validateIsolation($validator);
        });
    }

    /**
     * Valider la durée selon le type de dialyse
     */
    private function validateDuration($validator): void
    {
        if (!$this->has('date_debut') || !$this->has('date_fin') || !$this->has('type_dialyse')) {
            return;
        }

        $dateDebut = \Carbon\Carbon::parse($this->date_debut);
        $dateFin = \Carbon\Carbon::parse($this->date_fin);
        $duration = $dateDebut->diffInMinutes($dateFin);

        $durations = [
            'hemodialyse' => ['min' => 180, 'max' => 300],
            'hemodiafiltration' => ['min' => 180, 'max' => 300],
            'dialyse_peritoneale' => ['min' => 30, 'max' => 120],
        ];

        $typeDialyse = $this->type_dialyse;
        
        if (isset($durations[$typeDialyse])) {
            if ($duration < $durations[$typeDialyse]['min']) {
                $validator->errors()->add(
                    'date_fin',
                    "La durée minimale pour une {$typeDialyse} est de {$durations[$typeDialyse]['min']} minutes."
                );
            }
            
            if ($duration > $durations[$typeDialyse]['max']) {
                $validator->errors()->add(
                    'date_fin',
                    "La durée maximale pour une {$typeDialyse} est de {$durations[$typeDialyse]['max']} minutes."
                );
            }
        }
    }

    /**
     * Valider la disponibilité de la salle et du personnel
     */
    private function validateAvailability($validator): void
    {
        if (!$this->has('salle_id') || !$this->has('date_debut') || !$this->has('date_fin')) {
            return;
        }

        $planningService = app(PlanningService::class);
        
        $conflicts = $planningService->detectConflits([
            'salle_id' => $this->salle_id,
            'date_debut' => $this->date_debut,
            'date_fin' => $this->date_fin,
            'personnel_ids' => $this->personnel_ids ?? [],
            'type_dialyse' => $this->type_dialyse ?? 'hemodialyse',
            'isolement_requis' => $this->isolement_requis ?? false,
        ]);

        // Ajouter les conflits de type erreur à la validation
        foreach ($conflicts as $conflict) {
            if ($conflict['severity'] === 'error') {
                $field = match($conflict['type']) {
                    'salle_occupee' => 'salle_id',
                    'personnel_indisponible' => 'personnel_ids',
                    'isolement_non_disponible' => 'isolement_requis',
                    default => 'date_debut',
                };
                
                $validator->errors()->add($field, $conflict['message']);
            }
        }
    }

    /**
     * Valider la capacité de la salle
     */
    private function validateCapacity($validator): void
    {
        if (!$this->has('salle_id')) {
            return;
        }

        $salle = \App\Models\Salle::find($this->salle_id);
        
        if (!$salle) {
            return;
        }

        // Vérifier si le nombre de personnel ne dépasse pas la capacité
        $nombrePersonnel = count($this->personnel_ids ?? []);
        
        if ($nombrePersonnel > $salle->capacity) {
            $validator->errors()->add(
                'personnel_ids',
                "Le nombre de personnel ({$nombrePersonnel}) dépasse la capacité de la salle ({$salle->capacity})."
            );
        }
    }

    /**
     * Valider la compatibilité isolement
     */
    private function validateIsolation($validator): void
    {
        if (!$this->has('isolement_requis') || !$this->isolement_requis) {
            return;
        }

        if (!$this->has('salle_id')) {
            return;
        }

        $salle = \App\Models\Salle::find($this->salle_id);
        
        if (!$salle) {
            return;
        }

        if (!$salle->is_isolation) {
            $validator->errors()->add(
                'isolement_requis',
                "La salle {$salle->name} n'est pas équipée pour l'isolement."
            );
        }
    }

    /**
     * Messages de validation personnalisés
     */
    public function messages(): array
    {
        return [
            'salle_id.required' => 'La sélection d\'une salle est obligatoire.',
            'salle_id.exists' => 'La salle sélectionnée n\'existe pas.',
            
            'patient_reference.required' => 'La référence patient est obligatoire.',
            'patient_reference.regex' => 'La référence patient doit être au format : PAT-YYYY-NNN',
            
            'type_dialyse.required' => 'Le type de dialyse est obligatoire.',
            
            'date_debut.required' => 'La date et heure de début sont obligatoires.',
            'date_debut.after_or_equal' => 'La réservation ne peut être dans le passé.',
            
            'date_fin.required' => 'La date et heure de fin sont obligatoires.',
            'date_fin.after' => 'La date de fin doit être après la date de début.',
            
            'personnel_ids.required' => 'Au moins un membre du personnel doit être assigné.',
            'personnel_ids.min' => 'Au moins un membre du personnel doit être assigné.',
            'personnel_ids.*.exists' => 'Un membre du personnel sélectionné n\'existe pas.',
            
            'notes.max' => 'Les notes ne peuvent dépasser 1000 caractères.',
            'special_requirements.max' => 'Les besoins spéciaux ne peuvent dépasser 500 caractères.',
        ];
    }

    /**
     * Attributs personnalisés pour les messages d'erreur
     */
    public function attributes(): array
    {
        return [
            'salle_id' => 'salle',
            'patient_reference' => 'référence patient',
            'patient_initials' => 'initiales patient',
            'type_dialyse' => 'type de dialyse',
            'date_debut' => 'date de début',
            'date_fin' => 'date de fin',
            'personnel_ids' => 'personnel assigné',
            'isolement_requis' => 'isolement',
            'notes' => 'notes opérationnelles',
            'special_requirements' => 'besoins spéciaux',
            'equipements_speciaux' => 'équipements spéciaux',
        ];
    }
}