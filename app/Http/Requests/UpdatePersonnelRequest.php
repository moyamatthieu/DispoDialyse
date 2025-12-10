<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation pour la modification d'une fiche personnel
 */
class UpdatePersonnelRequest extends FormRequest
{
    /**
     * Déterminer si l'utilisateur est autorisé à faire cette requête
     */
    public function authorize(): bool
    {
        $personnel = $this->route('personnel');
        return $this->user()->can('update', $personnel);
    }

    /**
     * Règles de validation
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $personnelId = $this->route('personnel')->id;

        return [
            // Lien avec compte utilisateur
            'user_id' => [
                'nullable',
                'exists:users,id',
                Rule::unique('personnel', 'user_id')->ignore($personnelId)
            ],
            
            // Identité
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            
            // Informations professionnelles
            'job_title' => ['required', 'string', 'max:150'],
            'specialty' => ['nullable', 'string', 'max:100'],
            'department' => ['required', 'string', 'max:100'],
            'employment_type' => ['required', Rule::in(['full_time', 'part_time', 'contractor'])],
            
            // Contact
            'email_pro' => [
                'required',
                'email',
                'max:255',
                Rule::unique('personnel', 'email_pro')->ignore($personnelId)
            ],
            'phone_office' => ['nullable', 'regex:/^0[1-9]\d{8}$/'],
            'phone_mobile' => ['nullable', 'regex:/^0[67]\d{8}$/'],
            'phone_pager' => ['nullable', 'string', 'max:20'],
            'extension' => ['nullable', 'string', 'max:10'],
            
            // Compétences et qualifications (arrays JSON)
            'qualifications' => ['nullable', 'array'],
            'qualifications.*' => ['string', 'max:255'],
            'certifications' => ['nullable', 'array'],
            'certifications.*' => ['string', 'max:255'],
            'languages' => ['nullable', 'array'],
            'languages.*' => ['string', 'max:50'],
            
            // Photo
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // 2 MB max
            'remove_photo' => ['nullable', 'boolean'],
            
            // Dates et statut
            'hire_date' => ['required', 'date', 'before_or_equal:today'],
            'leave_date' => ['nullable', 'date', 'after:hire_date'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Messages d'erreur personnalisés
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom est obligatoire.',
            'job_title.required' => 'La fonction est obligatoire.',
            'department.required' => 'Le service est obligatoire.',
            'email_pro.required' => 'L\'email professionnel est obligatoire.',
            'email_pro.email' => 'L\'email doit être une adresse valide.',
            'email_pro.unique' => 'Cet email professionnel est déjà utilisé.',
            'phone_office.regex' => 'Le téléphone fixe doit être un numéro français valide (10 chiffres).',
            'phone_mobile.regex' => 'Le téléphone mobile doit être un numéro français valide (06 ou 07).',
            'photo.image' => 'Le fichier doit être une image.',
            'photo.max' => 'La photo ne doit pas dépasser 2 Mo.',
            'hire_date.required' => 'La date d\'embauche est obligatoire.',
            'hire_date.before_or_equal' => 'La date d\'embauche ne peut pas être dans le futur.',
            'leave_date.after' => 'La date de départ doit être après la date d\'embauche.',
            'user_id.unique' => 'Ce compte utilisateur est déjà lié à un autre membre du personnel.',
        ];
    }

    /**
     * Noms d'attributs personnalisés
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'prénom',
            'last_name' => 'nom',
            'job_title' => 'fonction',
            'specialty' => 'spécialité',
            'department' => 'service',
            'employment_type' => 'type de contrat',
            'email_pro' => 'email professionnel',
            'phone_office' => 'téléphone fixe',
            'phone_mobile' => 'téléphone mobile',
            'phone_pager' => 'bipeur',
            'extension' => 'extension',
            'qualifications' => 'qualifications',
            'certifications' => 'certifications',
            'languages' => 'langues parlées',
            'photo' => 'photo',
            'hire_date' => 'date d\'embauche',
            'leave_date' => 'date de départ',
            'is_active' => 'statut actif',
        ];
    }

    /**
     * Préparer les données pour validation
     */
    protected function prepareForValidation(): void
    {
        // Convertir is_active en booléen si présent
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        // Convertir remove_photo en booléen si présent
        if ($this->has('remove_photo')) {
            $this->merge([
                'remove_photo' => filter_var($this->remove_photo, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        // Nettoyer les tableaux vides
        if ($this->has('qualifications') && empty($this->qualifications)) {
            $this->merge(['qualifications' => null]);
        }
        if ($this->has('certifications') && empty($this->certifications)) {
            $this->merge(['certifications' => null]);
        }
        if ($this->has('languages') && empty($this->languages)) {
            $this->merge(['languages' => null]);
        }
    }
}