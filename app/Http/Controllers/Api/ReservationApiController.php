<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use App\Models\Salle;
use App\Services\PlanningService;
use App\Enums\StatutReservationEnum;
use App\Enums\TypeDialyseEnum;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

/**
 * Contrôleur API REST pour les réservations
 * 
 * Fournit une API RESTful complète pour la gestion des réservations
 * Utilisé par l'interface JavaScript et les intégrations tierces
 */
class ReservationApiController extends Controller
{
    /**
     * Service de planning
     */
    protected PlanningService $planningService;

    /**
     * Constructeur
     */
    public function __construct(PlanningService $planningService)
    {
        $this->planningService = $planningService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Liste paginée des réservations avec filtres
     * 
     * GET /api/reservations
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('planning.view');

        $query = Reservation::with(['salle', 'personnel', 'creator']);

        // Filtre par salle
        if ($request->has('salle_id')) {
            $query->where('salle_id', $request->salle_id);
        }

        // Filtre par type de dialyse
        if ($request->has('type_dialyse')) {
            $query->where('dialysis_type', $request->type_dialyse);
        }

        // Filtre par statut
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filtre par période
        if ($request->has('date_debut') && $request->has('date_fin')) {
            $query->betweenDates(
                Carbon::parse($request->date_debut),
                Carbon::parse($request->date_fin)
            );
        } elseif ($request->has('date')) {
            // Filtrer par jour spécifique
            $query->whereDate('start_time', $request->date);
        }

        // Filtre par patient
        if ($request->has('patient_reference')) {
            $query->where('patient_reference', $request->patient_reference);
        }

        // Recherche
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('patient_reference', 'like', "%{$search}%")
                  ->orWhere('patient_initials', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Tri
        $sortBy = $request->get('sort_by', 'start_time');
        $sortDir = $request->get('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $reservations = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => ReservationResource::collection($reservations),
            'meta' => [
                'current_page' => $reservations->currentPage(),
                'last_page' => $reservations->lastPage(),
                'per_page' => $reservations->perPage(),
                'total' => $reservations->total(),
            ],
        ]);
    }

    /**
     * Créer une nouvelle réservation
     * 
     * POST /api/reservations
     * 
     * @param StoreReservationRequest $request
     * @return JsonResponse
     */
    public function store(StoreReservationRequest $request): JsonResponse
    {
        try {
            $reservation = Reservation::create([
                'salle_id' => $request->salle_id,
                'start_time' => Carbon::parse($request->date_debut),
                'end_time' => Carbon::parse($request->date_fin),
                'patient_reference' => $request->patient_reference,
                'patient_initials' => $request->patient_initials,
                'dialysis_type' => TypeDialyseEnum::from($request->type_dialyse),
                'status' => StatutReservationEnum::SCHEDULED,
                'notes' => $request->notes,
                'special_requirements' => $this->formatSpecialRequirements($request),
                'created_by' => auth()->id(),
            ]);

            $reservation->personnel()->attach($request->personnel_ids);

            // Événement de création
            event(new \App\Events\ReservationCreated($reservation));

            $reservation->load(['salle', 'personnel', 'creator']);

            return response()->json([
                'success' => true,
                'message' => 'Réservation créée avec succès.',
                'data' => new ReservationResource($reservation),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Afficher les détails d'une réservation
     * 
     * GET /api/reservations/{id}
     * 
     * @param Reservation $reservation
     * @return JsonResponse
     */
    public function show(Reservation $reservation): JsonResponse
    {
        $this->authorize('planning.view');

        $reservation->load(['salle', 'personnel', 'creator', 'transmissions']);

        return response()->json([
            'success' => true,
            'data' => new ReservationResource($reservation),
        ]);
    }

    /**
     * Modifier une réservation
     * 
     * PUT /api/reservations/{id}
     * 
     * @param UpdateReservationRequest $request
     * @param Reservation $reservation
     * @return JsonResponse
     */
    public function update(UpdateReservationRequest $request, Reservation $reservation): JsonResponse
    {
        try {
            $reservation->update([
                'salle_id' => $request->salle_id,
                'start_time' => Carbon::parse($request->date_debut),
                'end_time' => Carbon::parse($request->date_fin),
                'patient_reference' => $request->patient_reference,
                'patient_initials' => $request->patient_initials,
                'dialysis_type' => TypeDialyseEnum::from($request->type_dialyse),
                'notes' => $request->notes,
                'special_requirements' => $this->formatSpecialRequirements($request),
            ]);

            $reservation->personnel()->sync($request->personnel_ids);

            // Événement de modification
            event(new \App\Events\ReservationUpdated($reservation));

            $reservation->load(['salle', 'personnel', 'creator']);

            return response()->json([
                'success' => true,
                'message' => 'Réservation modifiée avec succès.',
                'data' => new ReservationResource($reservation),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Annuler une réservation
     * 
     * DELETE /api/reservations/{id}
     * 
     * @param Request $request
     * @param Reservation $reservation
     * @return JsonResponse
     */
    public function destroy(Request $request, Reservation $reservation): JsonResponse
    {
        $this->authorize('planning.delete');

        $request->validate([
            'cancellation_reason' => 'required|string|min:10|max:500',
        ]);

        try {
            if (!$this->planningService->canCancelReservation($reservation, auth()->user())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette réservation ne peut pas être annulée.',
                ], 403);
            }

            $reservation->cancel($request->cancellation_reason, auth()->user());

            // Événement d'annulation
            event(new \App\Events\ReservationCancelled($reservation));

            return response()->json([
                'success' => true,
                'message' => 'Réservation annulée avec succès.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Déplacer une réservation
     * 
     * POST /api/reservations/{id}/move
     * 
     * @param Request $request
     * @param Reservation $reservation
     * @return JsonResponse
     */
    public function move(Request $request, Reservation $reservation): JsonResponse
    {
        $this->authorize('planning.edit');

        $request->validate([
            'date_debut' => 'required|date|after_or_equal:now',
            'date_fin' => 'required|date|after:date_debut',
            'salle_id' => 'sometimes|exists:salles,id',
        ]);

        try {
            $dateDebut = Carbon::parse($request->date_debut);
            $dateFin = Carbon::parse($request->date_fin);
            $salleId = $request->get('salle_id', $reservation->salle_id);

            // Vérifier les conflits
            $conflicts = $this->planningService->detectConflits([
                'salle_id' => $salleId,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'personnel_ids' => $reservation->personnel->pluck('id')->toArray(),
                'exclude_reservation_id' => $reservation->id,
            ]);

            $criticalConflicts = collect($conflicts)->where('severity', 'error');

            if ($criticalConflicts->isNotEmpty()) {
                return response()->json([
                    'success' => false,
                    'conflicts' => $criticalConflicts->pluck('message')->toArray(),
                ], 422);
            }

            // Mettre à jour
            $reservation->update([
                'salle_id' => $salleId,
                'start_time' => $dateDebut,
                'end_time' => $dateFin,
            ]);

            event(new \App\Events\ReservationUpdated($reservation));

            $reservation->load(['salle', 'personnel']);

            return response()->json([
                'success' => true,
                'message' => 'Réservation déplacée avec succès.',
                'data' => new ReservationResource($reservation),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Détecter les conflits pour un créneau
     * 
     * GET /api/reservations/conflicts
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function conflicts(Request $request): JsonResponse
    {
        $this->authorize('planning.view');

        $request->validate([
            'salle_id' => 'required|exists:salles,id',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'personnel_ids' => 'sometimes|array',
            'personnel_ids.*' => 'exists:personnel,id',
        ]);

        $conflicts = $this->planningService->detectConflits([
            'salle_id' => $request->salle_id,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'personnel_ids' => $request->personnel_ids ?? [],
            'type_dialyse' => $request->type_dialyse ?? 'hemodialyse',
            'isolement_requis' => $request->isolement_requis ?? false,
            'exclude_reservation_id' => $request->exclude_reservation_id ?? null,
        ]);

        $alternatives = [];
        
        if (!empty($conflicts)) {
            $alternatives = $this->planningService->suggestAlternatives([
                'salle_id' => $request->salle_id,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
            ]);
        }

        return response()->json([
            'success' => true,
            'has_conflicts' => !empty($conflicts),
            'conflicts' => $conflicts,
            'alternatives' => $alternatives,
        ]);
    }

    /**
     * Obtenir les disponibilités d'une salle
     * 
     * GET /api/reservations/availability
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function availability(Request $request): JsonResponse
    {
        $this->authorize('planning.view');

        $request->validate([
            'salle_id' => 'required|exists:salles,id',
            'date' => 'required|date',
            'duration' => 'required|integer|min:30|max:480', // 30min à 8h
        ]);

        $salle = Salle::find($request->salle_id);
        $date = Carbon::parse($request->date);
        $duration = $request->duration;

        $slots = $this->planningService->findAvailableSlots(
            $salle->id,
            $date->copy()->startOfDay(),
            $date->copy()->endOfDay(),
            $duration
        );

        return response()->json([
            'success' => true,
            'salle' => [
                'id' => $salle->id,
                'nom' => $salle->name,
            ],
            'date' => $date->format('Y-m-d'),
            'duration' => $duration,
            'available_slots' => collect($slots)->map(function ($slot) {
                return [
                    'start' => $slot['start']->format('Y-m-d H:i:s'),
                    'end' => $slot['end']->format('Y-m-d H:i:s'),
                    'start_time' => $slot['start']->format('H:i'),
                    'end_time' => $slot['end']->format('H:i'),
                ];
            })->toArray(),
        ]);
    }

    /**
     * Obtenir les statistiques d'une période
     * 
     * GET /api/reservations/stats
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function stats(Request $request): JsonResponse
    {
        $this->authorize('planning.view');

        $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'salle_id' => 'sometimes|exists:salles,id',
        ]);

        $dateDebut = Carbon::parse($request->date_debut);
        $dateFin = Carbon::parse($request->date_fin);

        $query = Reservation::betweenDates($dateDebut, $dateFin)
            ->whereNotIn('status', [StatutReservationEnum::CANCELLED, StatutReservationEnum::NO_SHOW]);

        if ($request->has('salle_id')) {
            $query->where('salle_id', $request->salle_id);
        }

        $reservations = $query->get();

        $stats = [
            'periode' => [
                'debut' => $dateDebut->format('d/m/Y'),
                'fin' => $dateFin->format('d/m/Y'),
            ],
            'total_reservations' => $reservations->count(),
            'par_type' => $reservations->groupBy('dialysis_type')
                ->map(fn($group) => $group->count())
                ->toArray(),
            'par_statut' => $reservations->groupBy('status')
                ->map(fn($group) => $group->count())
                ->toArray(),
            'duree_totale_heures' => round($reservations->sum('duration_in_minutes') / 60, 2),
            'duree_moyenne_minutes' => $reservations->count() > 0 
                ? round($reservations->avg('duration_in_minutes'), 0) 
                : 0,
        ];

        // Si une salle spécifique, ajouter le taux d'occupation
        if ($request->has('salle_id')) {
            $salle = Salle::find($request->salle_id);
            $stats['taux_occupation'] = round(
                $this->planningService->getTauxOccupation($salle, $dateDebut, $dateFin),
                2
            );
        }

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Formater les besoins spéciaux
     * 
     * @param Request $request
     * @return string|null
     */
    private function formatSpecialRequirements(Request $request): ?string
    {
        $requirements = [];

        if ($request->isolement_requis) {
            $requirements[] = 'Isolement requis';
        }

        if ($request->equipements_speciaux && is_array($request->equipements_speciaux)) {
            $requirements = array_merge($requirements, $request->equipements_speciaux);
        }

        if ($request->special_requirements) {
            $requirements[] = $request->special_requirements;
        }

        return !empty($requirements) ? implode(' | ', $requirements) : null;
    }
}