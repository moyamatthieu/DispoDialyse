<?php

namespace App\Http\Controllers;

use App\Models\Salle;
use App\Services\PlanningService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Carbon\Carbon;

/**
 * Contrôleur pour la gestion des salles de dialyse
 * 
 * Gère l'affichage, les statistiques et la disponibilité des salles
 */
class SalleController extends Controller
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
     * Afficher la liste des salles
     * 
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $this->authorize('planning.view');

        $query = Salle::query();

        // Filtre par statut
        if ($request->has('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        } else {
            // Par défaut, seulement les actives
            $query->active();
        }

        // Filtre par type (isolement)
        if ($request->has('type')) {
            if ($request->type === 'isolation') {
                $query->isolation();
            } elseif ($request->type === 'standard') {
                $query->standard();
            }
        }

        // Filtre par bâtiment
        if ($request->has('building') && $request->building) {
            $query->building($request->building);
        }

        // Filtre par étage
        if ($request->has('floor') && $request->floor) {
            $query->floor($request->floor);
        }

        // Recherche
        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        // Tri
        $sortBy = $request->get('sort_by', 'name');
        $sortDir = $request->get('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        $salles = $query->paginate(15);

        // Obtenir les valeurs uniques pour les filtres
        $buildings = Salle::active()
            ->whereNotNull('building')
            ->distinct()
            ->pluck('building')
            ->sort();

        $floors = Salle::active()
            ->whereNotNull('floor')
            ->distinct()
            ->pluck('floor')
            ->sort();

        return view('salles.index', compact('salles', 'buildings', 'floors'));
    }

    /**
     * Afficher les détails d'une salle avec statistiques
     * 
     * @param Salle $salle
     * @param Request $request
     * @return View
     */
    public function show(Salle $salle, Request $request): View
    {
        $this->authorize('planning.view');

        // Période pour les statistiques (par défaut: ce mois)
        $dateDebut = $request->has('date_debut') 
            ? Carbon::parse($request->date_debut)
            : now()->startOfMonth();
        
        $dateFin = $request->has('date_fin')
            ? Carbon::parse($request->date_fin)
            : now()->endOfMonth();

        // Obtenir les statistiques
        $statistiques = $this->planningService->getStatistiquesSalle($salle, $dateDebut, $dateFin);

        // Réservations du jour
        $reservationsAujourdhui = $salle->todayReservations()->get();

        // Réservations à venir (7 prochains jours)
        $reservationsAvenir = $salle->reservations()
            ->betweenDates(now(), now()->addDays(7))
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->orderBy('start_time')
            ->get();

        // Taux d'occupation cette semaine
        $tauxOccupationSemaine = $this->planningService->getTauxOccupation(
            $salle,
            now()->startOfWeek(),
            now()->endOfWeek()
        );

        // Taux d'occupation ce mois
        $tauxOccupationMois = $this->planningService->getTauxOccupation(
            $salle,
            now()->startOfMonth(),
            now()->endOfMonth()
        );

        return view('salles.show', compact(
            'salle',
            'statistiques',
            'reservationsAujourdhui',
            'reservationsAvenir',
            'tauxOccupationSemaine',
            'tauxOccupationMois',
            'dateDebut',
            'dateFin'
        ));
    }

    /**
     * Obtenir la disponibilité d'une salle (API JSON)
     * 
     * @param Salle $salle
     * @param Request $request
     * @return JsonResponse
     */
    public function availability(Salle $salle, Request $request): JsonResponse
    {
        $this->authorize('planning.view');

        $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
        ]);

        $dateDebut = Carbon::parse($request->date_debut);
        $dateFin = Carbon::parse($request->date_fin);

        $isAvailable = $this->planningService->isSalleAvailable(
            $salle->id,
            $dateDebut,
            $dateFin
        );

        $response = [
            'salle_id' => $salle->id,
            'salle_nom' => $salle->name,
            'is_available' => $isAvailable,
            'periode' => [
                'debut' => $dateDebut->format('Y-m-d H:i:s'),
                'fin' => $dateFin->format('Y-m-d H:i:s'),
            ],
        ];

        if (!$isAvailable) {
            // Récupérer la réservation conflictuelle
            $conflictingReservation = $salle->reservations()
                ->where(function ($q) use ($dateDebut, $dateFin) {
                    $q->whereBetween('start_time', [$dateDebut, $dateFin])
                      ->orWhereBetween('end_time', [$dateDebut, $dateFin])
                      ->orWhere(function ($q2) use ($dateDebut, $dateFin) {
                          $q2->where('start_time', '<=', $dateDebut)
                             ->where('end_time', '>=', $dateFin);
                      });
                })
                ->whereNotIn('status', ['cancelled', 'no_show'])
                ->first();

            if ($conflictingReservation) {
                $response['conflit'] = [
                    'reservation_id' => $conflictingReservation->id,
                    'patient_reference' => $conflictingReservation->patient_reference,
                    'debut' => $conflictingReservation->start_time->format('Y-m-d H:i:s'),
                    'fin' => $conflictingReservation->end_time->format('Y-m-d H:i:s'),
                ];
            }
        }

        return response()->json($response);
    }

    /**
     * Obtenir les statistiques d'une salle (API JSON)
     * 
     * @param Salle $salle
     * @param Request $request
     * @return JsonResponse
     */
    public function stats(Salle $salle, Request $request): JsonResponse
    {
        $this->authorize('planning.view');

        $request->validate([
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
        ]);

        $dateDebut = Carbon::parse($request->date_debut);
        $dateFin = Carbon::parse($request->date_fin);

        $statistiques = $this->planningService->getStatistiquesSalle($salle, $dateDebut, $dateFin);

        return response()->json([
            'success' => true,
            'salle' => [
                'id' => $salle->id,
                'nom' => $salle->name,
                'capacite' => $salle->capacity,
                'isolement' => $salle->is_isolation,
            ],
            'statistiques' => $statistiques,
        ]);
    }

    /**
     * Obtenir le planning d'une salle pour une journée (API JSON)
     * 
     * @param Salle $salle
     * @param Request $request
     * @return JsonResponse
     */
    public function daySchedule(Salle $salle, Request $request): JsonResponse
    {
        $this->authorize('planning.view');

        $request->validate([
            'date' => 'required|date',
        ]);

        $date = Carbon::parse($request->date);

        $reservations = $salle->reservations()
            ->whereDate('start_time', $date)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->orderBy('start_time')
            ->with(['personnel'])
            ->get();

        $schedule = $reservations->map(function ($reservation) {
            return [
                'id' => $reservation->id,
                'patient_reference' => $reservation->patient_reference,
                'patient_initials' => $reservation->patient_initials,
                'start_time' => $reservation->start_time->format('H:i'),
                'end_time' => $reservation->end_time->format('H:i'),
                'duration_minutes' => $reservation->duration_in_minutes,
                'type_dialyse' => $reservation->dialysis_type->value,
                'status' => $reservation->status->value,
                'personnel' => $reservation->personnel->map(fn($p) => [
                    'id' => $p->id,
                    'nom' => $p->full_name,
                    'fonction' => $p->job_title,
                ])->toArray(),
            ];
        });

        return response()->json([
            'success' => true,
            'salle' => [
                'id' => $salle->id,
                'nom' => $salle->name,
            ],
            'date' => $date->format('Y-m-d'),
            'jour' => $date->locale('fr')->isoFormat('dddd D MMMM YYYY'),
            'reservations' => $schedule,
            'nombre_reservations' => $schedule->count(),
        ]);
    }

    /**
     * Comparer plusieurs salles (API JSON)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function compare(Request $request): JsonResponse
    {
        $this->authorize('planning.view');

        $request->validate([
            'salle_ids' => 'required|array|min:2|max:5',
            'salle_ids.*' => 'exists:salles,id',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
        ]);

        $dateDebut = Carbon::parse($request->date_debut);
        $dateFin = Carbon::parse($request->date_fin);

        $comparaison = [];

        foreach ($request->salle_ids as $salleId) {
            $salle = Salle::find($salleId);
            
            if ($salle) {
                $stats = $this->planningService->getStatistiquesSalle($salle, $dateDebut, $dateFin);
                
                $comparaison[] = [
                    'salle' => [
                        'id' => $salle->id,
                        'nom' => $salle->name,
                        'capacite' => $salle->capacity,
                        'isolement' => $salle->is_isolation,
                    ],
                    'statistiques' => $stats,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'periode' => [
                'debut' => $dateDebut->format('d/m/Y'),
                'fin' => $dateFin->format('d/m/Y'),
            ],
            'comparaison' => $comparaison,
        ]);
    }
}