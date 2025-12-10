<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PersonnelDetailResource;
use App\Http\Resources\PersonnelResource;
use App\Models\Personnel;
use App\Services\PersonnelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Contrôleur API REST pour l'annuaire du personnel
 */
class PersonnelApiController extends Controller
{
    public function __construct(
        private PersonnelService $personnelService
    ) {
        $this->middleware('auth:sanctum');
    }

    /**
     * Liste du personnel avec filtres (API)
     * 
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Personnel::class);

        $query = Personnel::query()->active()->with('user');

        // Filtres
        if ($request->filled('service')) {
            $query->where('department', $request->service);
        }

        if ($request->filled('fonction')) {
            $query->where('job_title', 'like', '%' . $request->fonction . '%');
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $perPage = $request->get('per_page', 15);
        $personnel = $query->orderBy('last_name')->paginate($perPage);

        return PersonnelResource::collection($personnel);
    }

    /**
     * Détails d'un membre du personnel (API)
     * 
     * @param Personnel $personnel
     * @return PersonnelDetailResource
     */
    public function show(Personnel $personnel): PersonnelDetailResource
    {
        $this->authorize('view', $personnel);

        $personnel->load(['user', 'gardes']);

        return new PersonnelDetailResource($personnel);
    }

    /**
     * Recherche avec autocomplete
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Personnel::class);

        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $results = $this->personnelService->search(
            $request->q,
            $request->only(['fonction', 'service', 'disponibilite'])
        );

        return response()->json([
            'data' => PersonnelResource::collection($results),
            'count' => $results->count(),
        ]);
    }

    /**
     * Suggestions d'autocomplete
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Personnel::class);

        $request->validate([
            'q' => 'required|string|min:2',
        ]);

        $personnel = Personnel::active()
            ->search($request->q)
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'job_title', 'department', 'photo_url']);

        return response()->json(
            $personnel->map(fn($p) => [
                'id' => $p->id,
                'label' => $p->full_name,
                'fonction' => $p->job_title,
                'service' => $p->department,
                'photo' => $p->photo_url ?? '/images/default-avatar.png',
            ])
        );
    }

    /**
     * Personnel disponible actuellement
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function disponibles(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Personnel::class);

        $date = $request->get('date') ? \Carbon\Carbon::parse($request->date) : now();
        $personnel = $this->personnelService->findDisponibles($date);

        return response()->json([
            'data' => PersonnelResource::collection($personnel),
            'date' => $date->toIso8601String(),
            'count' => $personnel->count(),
        ]);
    }

    /**
     * Trouver personnel par compétence
     * 
     * @param string $competence
     * @return JsonResponse
     */
    public function parCompetence(string $competence): JsonResponse
    {
        $this->authorize('viewAny', Personnel::class);

        $personnel = $this->personnelService->findByCompetence($competence);

        return response()->json([
            'data' => PersonnelResource::collection($personnel),
            'competence' => $competence,
            'count' => $personnel->count(),
        ]);
    }

    /**
     * Personnel de garde actuellement
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function deGarde(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Personnel::class);

        $date = $request->get('date') ? \Carbon\Carbon::parse($request->date) : now();
        $personnel = $this->personnelService->findDeGarde($date);

        return response()->json([
            'data' => PersonnelResource::collection($personnel),
            'date' => $date->toIso8601String(),
            'count' => $personnel->count(),
        ]);
    }

    /**
     * Statistiques d'un service
     * 
     * @param string $service
     * @return JsonResponse
     */
    public function statistiquesService(string $service): JsonResponse
    {
        $this->authorize('viewAny', Personnel::class);

        $stats = $this->personnelService->getStatistiquesService($service);

        return response()->json($stats);
    }

    /**
     * Données pour l'organigramme
     * 
     * @return JsonResponse
     */
    public function organigramme(): JsonResponse
    {
        $this->authorize('viewAny', Personnel::class);

        $organigramme = $this->personnelService->buildOrganigramme();

        return response()->json($organigramme);
    }
}