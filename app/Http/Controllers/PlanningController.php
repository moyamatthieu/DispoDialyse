<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Models\Reservation;
use App\Models\Salle;
use App\Models\Personnel;
use App\Services\PlanningService;
use App\Enums\StatutReservationEnum;
use App\Enums\TypeDialyseEnum;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Carbon\Carbon;

/**
 * Contrôleur principal du module Planning des Salles de Dialyse
 * 
 * Gère toutes les opérations CRUD sur les réservations
 * et fournit les données pour l'interface FullCalendar
 */
class PlanningController extends Controller
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
    }

    /**
     * Afficher la vue principale du planning
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $this->authorize('planning.view');

        // Récupérer toutes les salles actives pour les filtres
        $salles = Salle::active()->orderBy('name')->get();
        
        // Récupérer le personnel actif pour l'assignation
        $personnel = Personnel::active()->orderBy('last_name')->get();
        
        // Types de dialyse
        $typesDialyse = TypeDialyseEnum::cases();
        
        // Filtres appliqués
        $filters = [
            'salle_id' => $request->get('salle_id'),
            'type_dialyse' => $request->get('type_dialyse'),
            'date' => $request->get('date', now()->format('Y-m-d')),
        ];

        return view('planning.index', compact('salles', 'personnel', 'typesDialyse', 'filters'));
    }

    /**
     * Fournir les données JSON pour FullCalendar
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function calendar(Request $request): JsonResponse
    {
        $this->authorize('planning.view');

        $start = Carbon::parse($request->get('start'));
        $end = Carbon::parse($request->get('end'));
        
        // Construire la requête avec filtres
        $query = Reservation::with(['salle', 'personnel'])
            ->betweenDates($start, $end)
            ->whereNotIn('status', [StatutReservationEnum::CANCELLED, StatutReservationEnum::NO_SHOW]);

        // Filtre par salle
        if ($request->has('salle_id') && $request->salle_id) {
            $query->where('salle_id', $request->salle_id);
        }

        // Filtre par type de dialyse
        if ($request->has('type_dialyse') && $request->type_dialyse) {
            $query->where('dialysis_type', $request->type_dialyse);
        }

        $reservations = $query->get();

        // Formatter pour FullCalendar
        $events = $reservations->map(function ($reservation) {
            return [
                'id' => $reservation->id,
                'title' => $reservation->patient_initials ?? $reservation->patient_reference,
                'start' => $reservation->start_time->toIso8601String(),
                'end' => $reservation->end_time->toIso8601String(),
                'backgroundColor' => $this->getColorByType($reservation->dialysis_type),
                'borderColor' => $this->getColorByType($reservation->dialysis_type),
                'extendedProps' => [
                    'salle_nom' => $reservation->salle->name,
                    'salle_id' => $reservation->salle_id,
                    'type_dialyse' => $reservation->dialysis_type->value,
                    'status' => $reservation->status->value,
                    'personnel' => $reservation->personnel->map(fn($p) => [
                        'id' => $p->id,
                        'nom' => $p->full_name,
                    ])->toArray(),
                    'notes' => $reservation->notes,
                    'isolement' => $reservation->special_requirements ? str_contains($reservation->special_requirements, 'isolement') : false,
                ],
                'editable' => $reservation->isEditable(),
                'url' => route('planning.show', $reservation),
            ];
        });

        return response()->json($events);
    }

    /**
     * Afficher les détails d'une réservation
     * 
     * @param Reservation $reservation
     * @return View
     */
    public function show(Reservation $reservation): View
    {
        $this->authorize('planning.view');

        $reservation->load(['salle', 'personnel', 'creator', 'transmissions']);

        return view('planning.show', compact('reservation'));
    }

    /**
     * Créer une nouvelle réservation
     * 
     * @param StoreReservationRequest $request
     * @return RedirectResponse
     */
    public function store(StoreReservationRequest $request): RedirectResponse
    {
        try {
            // Créer la réservation
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

            // Attacher le personnel
            $reservation->personnel()->attach($request->personnel_ids);

            // Événement de création (pour WebSocket)
            event(new \App\Events\ReservationCreated($reservation));

            return redirect()
                ->route('planning.index')
                ->with('success', 'Réservation créée avec succès.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la création de la réservation : ' . $e->getMessage());
        }
    }

    /**
     * Modifier une réservation existante
     * 
     * @param UpdateReservationRequest $request
     * @param Reservation $reservation
     * @return RedirectResponse
     */
    public function update(UpdateReservationRequest $request, Reservation $reservation): RedirectResponse
    {
        try {
            // Mettre à jour la réservation
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

            // Synchroniser le personnel
            $reservation->personnel()->sync($request->personnel_ids);

            // Événement de modification
            event(new \App\Events\ReservationUpdated($reservation));

            return redirect()
                ->route('planning.show', $reservation)
                ->with('success', 'Réservation modifiée avec succès.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erreur lors de la modification : ' . $e->getMessage());
        }
    }

    /**
     * Annuler une réservation (soft delete)
     * 
     * @param Request $request
     * @param Reservation $reservation
     * @return RedirectResponse
     */
    public function destroy(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->authorize('planning.delete');

        // Valider le motif d'annulation
        $request->validate([
            'cancellation_reason' => 'required|string|min:10|max:500',
        ], [
            'cancellation_reason.required' => 'Le motif d\'annulation est obligatoire.',
            'cancellation_reason.min' => 'Le motif doit contenir au moins 10 caractères.',
        ]);

        try {
            // Vérifier si l'annulation est autorisée
            if (!$this->planningService->canCancelReservation($reservation, auth()->user())) {
                return back()->with('error', 'Cette réservation ne peut pas être annulée.');
            }

            // Annuler la réservation
            $reservation->cancel($request->cancellation_reason, auth()->user());

            // Événement d'annulation
            event(new \App\Events\ReservationCancelled($reservation));

            return redirect()
                ->route('planning.index')
                ->with('success', 'Réservation annulée avec succès.');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'annulation : ' . $e->getMessage());
        }
    }

    /**
     * Dupliquer une réservation (formulaire pré-rempli)
     * 
     * @param Reservation $reservation
     * @return View
     */
    public function duplicate(Reservation $reservation): View
    {
        $this->authorize('planning.create');

        $reservation->load(['salle', 'personnel']);
        
        $salles = Salle::active()->orderBy('name')->get();
        $personnel = Personnel::active()->orderBy('last_name')->get();
        $typesDialyse = TypeDialyseEnum::cases();

        return view('planning.duplicate', compact('reservation', 'salles', 'personnel', 'typesDialyse'));
    }

    /**
     * Déplacer une réservation via drag & drop
     * 
     * @param Request $request
     * @param Reservation $reservation
     * @return JsonResponse
     */
    public function move(Request $request, Reservation $reservation): JsonResponse
    {
        $this->authorize('planning.edit');

        // Valider les nouvelles dates
        $request->validate([
            'date_debut' => 'required|date|after_or_equal:now',
            'date_fin' => 'required|date|after:date_debut',
        ]);

        try {
            $dateDebut = Carbon::parse($request->date_debut);
            $dateFin = Carbon::parse($request->date_fin);

            // Vérifier les conflits
            $conflicts = $this->planningService->detectConflits([
                'salle_id' => $reservation->salle_id,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'personnel_ids' => $reservation->personnel->pluck('id')->toArray(),
                'exclude_reservation_id' => $reservation->id,
            ]);

            // Filtrer uniquement les erreurs critiques
            $criticalConflicts = collect($conflicts)->where('severity', 'error');

            if ($criticalConflicts->isNotEmpty()) {
                return response()->json([
                    'success' => false,
                    'conflicts' => $criticalConflicts->pluck('message')->toArray(),
                ], 422);
            }

            // Mettre à jour les dates
            $reservation->update([
                'start_time' => $dateDebut,
                'end_time' => $dateFin,
            ]);

            // Événement de modification
            event(new \App\Events\ReservationUpdated($reservation));

            return response()->json([
                'success' => true,
                'message' => 'Réservation déplacée avec succès.',
                'reservation' => [
                    'id' => $reservation->id,
                    'start_time' => $reservation->start_time->toIso8601String(),
                    'end_time' => $reservation->end_time->toIso8601String(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du déplacement : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Vérifier les conflits pour un créneau donné
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function conflicts(Request $request): JsonResponse
    {
        $this->authorize('planning.view');

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
        
        // Si des conflits existent, suggérer des alternatives
        if (!empty($conflicts)) {
            $alternatives = $this->planningService->suggestAlternatives([
                'salle_id' => $request->salle_id,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
            ]);
        }

        return response()->json([
            'conflicts' => $conflicts,
            'alternatives' => $alternatives,
        ]);
    }

    /**
     * Obtenir les couleurs selon le type de dialyse
     * 
     * @param TypeDialyseEnum $type
     * @return string
     */
    private function getColorByType(TypeDialyseEnum $type): string
    {
        return match($type) {
            TypeDialyseEnum::HEMODIALYSIS => '#3b82f6', // Bleu
            TypeDialyseEnum::HEMODIAFILTRATION => '#8b5cf6', // Violet
            TypeDialyseEnum::PERITONEAL_DIALYSIS => '#10b981', // Vert
            default => '#6b7280', // Gris
        };
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