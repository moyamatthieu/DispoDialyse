<?php

namespace App\Services;

use App\Models\Personnel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

/**
 * Service métier pour la gestion du personnel
 */
class PersonnelService
{
    /**
     * Recherche intelligente de personnel
     * 
     * @param string $query Terme de recherche
     * @param array $filters Filtres additionnels
     * @return Collection
     */
    public function search(string $query, array $filters = []): Collection
    {
        $personnelQuery = Personnel::query()->active();

        // Recherche textuelle
        $personnelQuery->search($query);

        // Appliquer les filtres
        if (!empty($filters['fonction'])) {
            $personnelQuery->where('job_title', 'like', '%' . $filters['fonction'] . '%');
        }

        if (!empty($filters['service'])) {
            $personnelQuery->where('department', $filters['service']);
        }

        if (!empty($filters['disponibilite'])) {
            if ($filters['disponibilite'] === 'disponible') {
                $personnelQuery->where('is_active', true);
            } elseif ($filters['disponibilite'] === 'en_garde') {
                $personnelQuery->whereHas('gardes', function ($q) {
                    $q->where('start_datetime', '<=', now())
                      ->where('end_datetime', '>=', now())
                      ->where('status', 'confirmed');
                });
            }
        }

        return $personnelQuery->orderBy('last_name')->get();
    }

    /**
     * Trouver personnel ayant une compétence donnée
     * 
     * @param string $competence
     * @return Collection
     */
    public function findByCompetence(string $competence): Collection
    {
        return Personnel::active()
            ->where(function ($query) use ($competence) {
                $query->whereJsonContains('qualifications', $competence)
                      ->orWhereJsonContains('certifications', $competence);
            })
            ->orderBy('last_name')
            ->get();
    }

    /**
     * Trouver personnel disponible à une date/heure donnée
     * 
     * @param Carbon|null $date
     * @return Collection
     */
    public function findDisponibles(?Carbon $date = null): Collection
    {
        $date = $date ?? now();

        return Personnel::active()
            ->whereDoesntHave('gardes', function ($query) use ($date) {
                $query->where('start_datetime', '<=', $date)
                      ->where('end_datetime', '>=', $date);
            })
            ->orderBy('department')
            ->orderBy('last_name')
            ->get();
    }

    /**
     * Trouver personnel de garde à une date donnée
     * 
     * @param Carbon|null $date
     * @return Collection
     */
    public function findDeGarde(?Carbon $date = null): Collection
    {
        $date = $date ?? now();

        return Personnel::active()
            ->whereHas('gardes', function ($query) use ($date) {
                $query->where('start_datetime', '<=', $date)
                      ->where('end_datetime', '>=', $date)
                      ->where('status', 'confirmed');
            })
            ->with(['gardes' => function ($query) use ($date) {
                $query->where('start_datetime', '<=', $date)
                      ->where('end_datetime', '>=', $date)
                      ->where('status', 'confirmed');
            }])
            ->orderBy('last_name')
            ->get();
    }

    /**
     * Obtenir les statistiques d'un service
     * 
     * @param string $service
     * @return array
     */
    public function getStatistiquesService(string $service): array
    {
        $personnel = Personnel::where('department', $service)->get();
        $actifs = $personnel->where('is_active', true);

        $fonctions = $actifs->groupBy('job_title')->map(fn($group) => $group->count());

        return [
            'effectif_total' => $personnel->count(),
            'effectif_actif' => $actifs->count(),
            'effectif_inactif' => $personnel->count() - $actifs->count(),
            'repartition_fonctions' => $fonctions->toArray(),
            'taux_activite' => $personnel->count() > 0 
                ? round(($actifs->count() / $personnel->count()) * 100, 1) 
                : 0,
            'personnel_avec_compte' => $actifs->whereNotNull('user_id')->count(),
            'langues_parlees' => $this->getLanguesService($actifs),
        ];
    }

    /**
     * Obtenir les langues parlées dans un service
     * 
     * @param Collection $personnel
     * @return array
     */
    private function getLanguesService(Collection $personnel): array
    {
        $langues = [];
        
        foreach ($personnel as $p) {
            if ($p->languages) {
                foreach ($p->languages as $langue) {
                    if (!isset($langues[$langue])) {
                        $langues[$langue] = 0;
                    }
                    $langues[$langue]++;
                }
            }
        }

        arsort($langues);
        return $langues;
    }

    /**
     * Calculer le taux de présence d'un personnel sur une période
     * 
     * @param Personnel $personnel
     * @param Carbon $debut
     * @param Carbon $fin
     * @return float
     */
    public function getTauxPresence(Personnel $personnel, Carbon $debut, Carbon $fin): float
    {
        $joursTotal = $debut->diffInDays($fin);
        
        if ($joursTotal === 0) {
            return 100.0;
        }

        // Compter les jours de garde (considérés comme présence)
        $joursGarde = $personnel->gardes()
            ->where('start_datetime', '>=', $debut)
            ->where('start_datetime', '<=', $fin)
            ->where('status', 'confirmed')
            ->count();

        // Pour un calcul plus précis, on pourrait intégrer les absences, congés, etc.
        // Ici, on utilise les gardes comme proxy de présence
        
        return $joursTotal > 0 ? round(($joursGarde / $joursTotal) * 100, 1) : 0.0;
    }

    /**
     * Construire l'arbre hiérarchique pour l'organigramme
     * 
     * @return array
     */
    public function buildOrganigramme(): array
    {
        $personnel = Personnel::active()
            ->with('user')
            ->orderBy('department')
            ->orderBy('job_title')
            ->orderBy('last_name')
            ->get();

        // Grouper par service
        $services = $personnel->groupBy('department');

        $organigramme = [];

        foreach ($services as $service => $membres) {
            // Grouper par fonction dans chaque service
            $fonctions = $membres->groupBy('job_title');

            $serviceNode = [
                'nom' => $service,
                'effectif' => $membres->count(),
                'fonctions' => [],
            ];

            foreach ($fonctions as $fonction => $personnes) {
                $serviceNode['fonctions'][] = [
                    'nom' => $fonction,
                    'effectif' => $personnes->count(),
                    'personnel' => $personnes->map(function ($p) {
                        return [
                            'id' => $p->id,
                            'nom_complet' => $p->full_name,
                            'photo' => $p->photo_url ?? '/images/default-avatar.png',
                            'telephone' => $p->primary_phone,
                            'email' => $p->email_pro,
                        ];
                    })->toArray(),
                ];
            }

            $organigramme[] = $serviceNode;
        }

        return $organigramme;
    }

    /**
     * Upload et traitement de la photo de profil
     * 
     * @param Personnel $personnel
     * @param UploadedFile $file
     * @return string URL de la photo
     */
    public function uploadPhoto(Personnel $personnel, UploadedFile $file): string
    {
        // Créer un nom de fichier unique
        $filename = 'personnel_' . ($personnel->id ?? uniqid()) . '_' . time() . '.jpg';
        $path = 'photos/personnel/' . $filename;

        // Redimensionner et optimiser l'image avec Intervention Image si disponible
        try {
            if (class_exists('Intervention\Image\Facades\Image')) {
                $image = Image::make($file)
                    ->fit(400, 400) // Redimensionner en carré 400x400
                    ->encode('jpg', 85); // Encoder en JPEG qualité 85%

                Storage::disk('public')->put($path, $image->__toString());
            } else {
                // Fallback: sauvegarde simple sans traitement
                $file->storeAs('photos/personnel', $filename, 'public');
            }
        } catch (\Exception $e) {
            // En cas d'erreur avec Intervention Image, utiliser la méthode standard
            $file->storeAs('photos/personnel', $filename, 'public');
        }

        return Storage::url($path);
    }

    /**
     * Supprimer la photo d'un personnel
     * 
     * @param Personnel $personnel
     * @return bool
     */
    public function deletePhoto(Personnel $personnel): bool
    {
        if (!$personnel->photo_url) {
            return false;
        }

        // Extraire le chemin du fichier depuis l'URL
        $path = str_replace('/storage/', '', $personnel->photo_url);

        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }

    /**
     * Exporter l'annuaire en CSV
     * 
     * @param Collection $personnel
     * @return string Chemin du fichier CSV généré
     */
    public function exportToCsv(Collection $personnel): string
    {
        $filename = 'annuaire_export_' . now()->format('Y-m-d_His') . '.csv';
        $path = storage_path('app/exports/' . $filename);

        // Créer le répertoire si nécessaire
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $file = fopen($path, 'w');

        // En-têtes UTF-8 avec BOM pour Excel
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // En-têtes du CSV
        fputcsv($file, [
            'ID',
            'Prénom',
            'Nom',
            'Fonction',
            'Spécialité',
            'Service',
            'Type de contrat',
            'Email',
            'Téléphone fixe',
            'Téléphone mobile',
            'Bipeur',
            'Extension',
            'Qualifications',
            'Certifications',
            'Langues',
            'Date d\'embauche',
            'Ancienneté',
            'Statut',
        ], ';');

        // Données
        foreach ($personnel as $p) {
            fputcsv($file, [
                $p->id,
                $p->first_name,
                $p->last_name,
                $p->job_title,
                $p->specialty ?? '',
                $p->department,
                $this->getEmploymentTypeLabel($p->employment_type),
                $p->email_pro,
                $p->phone_office ?? '',
                $p->phone_mobile ?? '',
                $p->phone_pager ?? '',
                $p->extension ?? '',
                $p->qualifications ? implode(', ', $p->qualifications) : '',
                $p->certifications ? implode(', ', $p->certifications) : '',
                $p->languages ? implode(', ', $p->languages) : '',
                $p->hire_date ? $p->hire_date->format('d/m/Y') : '',
                $p->hire_date ? $p->hire_date->diffForHumans(null, true) : '',
                $p->is_active ? 'Actif' : 'Inactif',
            ], ';');
        }

        fclose($file);

        return $path;
    }

    /**
     * Obtenir le libellé du type de contrat
     * 
     * @param string $type
     * @return string
     */
    private function getEmploymentTypeLabel(string $type): string
    {
        return match($type) {
            'full_time' => 'Temps plein',
            'part_time' => 'Temps partiel',
            'contractor' => 'Contractuel',
            default => $type,
        };
    }
}