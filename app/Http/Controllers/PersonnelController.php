<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonnelRequest;
use App\Http\Requests\UpdatePersonnelRequest;
use App\Models\Personnel;
use App\Services\PersonnelService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Contrôleur principal de l'annuaire du personnel
 */
class PersonnelController extends Controller
{
    public function __construct(
        private PersonnelService $personnelService
    ) {
        // Appliquer les autorisations via Policy
        $this->authorizeResource(Personnel::class, 'personnel');
    }

    /**
     * Afficher la liste du personnel avec recherche et filtres
     */
    public function index(Request $request): View
    {
        $query = Personnel::query()->active()->with('user');

        // Recherche textuelle
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filtre par fonction
        if ($request->filled('fonction')) {
            $query->where('job_title', 'like', '%' . $request->fonction . '%');
        }

        // Filtre par service
        if ($request->filled('service')) {
            $query->where('department', $request->service);
        }

        // Filtre par disponibilité
        if ($request->filled('disponibilite')) {
            if ($request->disponibilite === 'disponible') {
                $query->where('is_active', true);
            } elseif ($request->disponibilite === 'en_garde') {
                $query->whereHas('gardes', function ($q) {
                    $q->where('start_datetime', '<=', now())
                      ->where('end_datetime', '>=', now())
                      ->where('status', 'confirmed');
                });
            }
        }

        // Tri
        $sortBy = $request->get('sort', 'last_name');
        $sortDir = $request->get('direction', 'asc');
        $query->orderBy($sortBy, $sortDir);

        $personnel = $query->paginate(20)->withQueryString();

        return view('annuaire.index', compact('personnel'));
    }

    /**
     * Afficher la fiche détaillée d'un membre du personnel
     */
    public function show(Personnel $personnel): View
    {
        $personnel->load(['user', 'gardes' => function ($query) {
            $query->where('start_datetime', '>=', now()->subDays(30))
                  ->orderBy('start_datetime', 'desc')
                  ->limit(10);
        }]);

        // Calculer statistiques
        $stats = [
            'anciennete' => $personnel->hire_date 
                ? $personnel->hire_date->diffForHumans(null, true) 
                : 'Non renseigné',
            'gardes_mois' => $personnel->gardes()
                ->where('start_datetime', '>=', now()->startOfMonth())
                ->where('start_datetime', '<=', now()->endOfMonth())
                ->count(),
            'est_de_garde' => $personnel->isOnCall(),
        ];

        return view('annuaire.show', compact('personnel', 'stats'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create(): View
    {
        // Récupérer les managers potentiels (personnel avec fonction de responsable)
        $managers = Personnel::active()
            ->whereIn('job_title', ['Chef de Service', 'Cadre de Santé', 'Médecin Chef'])
            ->orderBy('last_name')
            ->get();

        return view('annuaire.create', compact('managers'));
    }

    /**
     * Enregistrer un nouveau membre du personnel
     */
    public function store(StorePersonnelRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Gérer l'upload de la photo
        if ($request->hasFile('photo')) {
            $data['photo_url'] = $this->personnelService->uploadPhoto(
                new Personnel(),
                $request->file('photo')
            );
        }

        $personnel = Personnel::create($data);

        return redirect()
            ->route('annuaire.show', $personnel)
            ->with('success', 'Fiche personnel créée avec succès.');
    }

    /**
     * Afficher le formulaire de modification
     */
    public function edit(Personnel $personnel): View
    {
        $managers = Personnel::active()
            ->where('id', '!=', $personnel->id)
            ->whereIn('job_title', ['Chef de Service', 'Cadre de Santé', 'Médecin Chef'])
            ->orderBy('last_name')
            ->get();

        return view('annuaire.edit', compact('personnel', 'managers'));
    }

    /**
     * Mettre à jour la fiche personnel
     */
    public function update(UpdatePersonnelRequest $request, Personnel $personnel): RedirectResponse
    {
        $data = $request->validated();

        // Gérer l'upload de la nouvelle photo
        if ($request->hasFile('photo')) {
            // Supprimer l'ancienne photo si elle existe
            if ($personnel->photo_url) {
                $this->personnelService->deletePhoto($personnel);
            }
            
            $data['photo_url'] = $this->personnelService->uploadPhoto(
                $personnel,
                $request->file('photo')
            );
        }

        // Supprimer la photo si demandé
        if ($request->get('remove_photo') === '1' && $personnel->photo_url) {
            $this->personnelService->deletePhoto($personnel);
            $data['photo_url'] = null;
        }

        $personnel->update($data);

        return redirect()
            ->route('annuaire.show', $personnel)
            ->with('success', 'Fiche personnel mise à jour avec succès.');
    }

    /**
     * Archiver un membre du personnel (soft delete)
     */
    public function destroy(Personnel $personnel): RedirectResponse
    {
        $personnel->update(['is_active' => false]);
        $personnel->delete();

        return redirect()
            ->route('annuaire.index')
            ->with('success', 'Fiche personnel archivée avec succès.');
    }

    /**
     * Afficher l'organigramme interactif
     */
    public function organigramme(): View
    {
        $organigramme = $this->personnelService->buildOrganigramme();

        return view('annuaire.organigramme', compact('organigramme'));
    }

    /**
     * Afficher le trombinoscope
     */
    public function trombinoscope(): View
    {
        $personnel = Personnel::active()
            ->orderBy('department')
            ->orderBy('last_name')
            ->get();

        return view('annuaire.trombinoscope', compact('personnel'));
    }

    /**
     * Exporter l'annuaire en CSV
     */
    public function export(Request $request): BinaryFileResponse
    {
        $this->authorize('viewAny', Personnel::class);

        $query = Personnel::query()->active();

        // Appliquer les mêmes filtres que l'index
        if ($request->filled('search')) {
            $query->search($request->search);
        }
        if ($request->filled('service')) {
            $query->where('department', $request->service);
        }

        $personnel = $query->orderBy('last_name')->get();
        $csvPath = $this->personnelService->exportToCsv($personnel);

        return response()->download($csvPath, 'annuaire-personnel-' . now()->format('Y-m-d') . '.csv')
            ->deleteFileAfterSend();
    }

    /**
     * Obtenir la disponibilité actuelle d'un personnel (API JSON)
     */
    public function disponibilite(Personnel $personnel)
    {
        $this->authorize('view', $personnel);

        return response()->json([
            'est_actif' => $personnel->is_active,
            'est_de_garde' => $personnel->isOnCall(),
            'prochaine_garde' => $personnel->gardes()
                ->where('start_datetime', '>', now())
                ->orderBy('start_datetime')
                ->first(),
        ]);
    }
}