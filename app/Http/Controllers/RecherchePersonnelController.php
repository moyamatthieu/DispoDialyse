<?php

namespace App\Http\Controllers;

use App\Models\Personnel;
use App\Services\PersonnelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Contrôleur dédié à la recherche avancée de personnel
 */
class RecherchePersonnelController extends Controller
{
    public function __construct(
        private PersonnelService $personnelService
    ) {
        $this->middleware('auth');
        $this->middleware('can:personnel.view');
    }

    /**
     * Recherche multi-critères avec fuzzy search
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2',
            'fonction' => 'nullable|string',
            'service' => 'nullable|string',
            'disponibilite' => 'nullable|in:disponible,en_garde,tous',
        ]);

        $results = $this->personnelService->search(
            $request->q,
            $request->only(['fonction', 'service', 'disponibilite'])
        );

        return response()->json([
            'success' => true,
            'data' => $results->map(function ($personnel) {
                return [
                    'id' => $personnel->id,
                    'nom_complet' => $personnel->full_name,
                    'fonction' => $personnel->job_title,
                    'service' => $personnel->department,
                    'specialite' => $personnel->specialty,
                    'email' => $personnel->email_pro,
                    'telephone' => $personnel->primary_phone,
                    'photo' => $personnel->photo_url ?? '/images/default-avatar.png',
                    'est_actif' => $personnel->is_active,
                    'est_de_garde' => $personnel->isOnCall(),
                    'url' => route('annuaire.show', $personnel),
                ];
            }),
            'count' => $results->count(),
            'query' => $request->q,
        ]);
    }

    /**
     * Suggestions instantanées pour autocomplete
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2',
            'limit' => 'nullable|integer|min:1|max:20',
        ]);

        $limit = $request->get('limit', 10);

        $personnel = Personnel::active()
            ->search($request->q)
            ->limit($limit)
            ->get(['id', 'first_name', 'last_name', 'job_title', 'department', 'photo_url']);

        return response()->json(
            $personnel->map(fn($p) => [
                'id' => $p->id,
                'label' => $p->full_name,
                'sublabel' => "{$p->job_title} - {$p->department}",
                'photo' => $p->photo_url ?? '/images/default-avatar.png',
                'value' => $p->id,
            ])
        );
    }

    /**
     * Trouver personnel avec compétence spécifique
     * 
     * @param string $competence
     * @return JsonResponse
     */
    public function parCompetence(string $competence): JsonResponse
    {
        $personnel = $this->personnelService->findByCompetence($competence);

        return response()->json([
            'success' => true,
            'data' => $personnel->map(function ($p) {
                return [
                    'id' => $p->id,
                    'nom_complet' => $p->full_name,
                    'fonction' => $p->job_title,
                    'service' => $p->department,
                    'competences' => $p->qualifications,
                    'url' => route('annuaire.show', $p),
                ];
            }),
            'competence' => $competence,
            'count' => $personnel->count(),
        ]);
    }

    /**
     * Personnel disponible en ce moment
     * 
     * @return JsonResponse
     */
    public function disponibles(): JsonResponse
    {
        $personnel = $this->personnelService->findDisponibles();

        return response()->json([
            'success' => true,
            'data' => $personnel->map(function ($p) {
                return [
                    'id' => $p->id,
                    'nom_complet' => $p->full_name,
                    'fonction' => $p->job_title,
                    'service' => $p->department,
                    'telephone' => $p->primary_phone,
                    'email' => $p->email_pro,
                    'photo' => $p->photo_url ?? '/images/default-avatar.png',
                ];
            }),
            'date' => now()->toIso8601String(),
            'count' => $personnel->count(),
        ]);
    }

    /**
     * Personnel de garde aujourd'hui
     * 
     * @return JsonResponse
     */
    public function deGarde(): JsonResponse
    {
        $personnel = $this->personnelService->findDeGarde();

        return response()->json([
            'success' => true,
            'data' => $personnel->map(function ($p) {
                $gardeActuelle = $p->gardes()
                    ->where('start_datetime', '<=', now())
                    ->where('end_datetime', '>=', now())
                    ->where('status', 'confirmed')
                    ->first();

                return [
                    'id' => $p->id,
                    'nom_complet' => $p->full_name,
                    'fonction' => $p->job_title,
                    'service' => $p->department,
                    'telephone' => $p->primary_phone,
                    'photo' => $p->photo_url ?? '/images/default-avatar.png',
                    'garde' => $gardeActuelle ? [
                        'debut' => $gardeActuelle->start_datetime->format('H:i'),
                        'fin' => $gardeActuelle->end_datetime->format('H:i'),
                        'type' => $gardeActuelle->shift_type,
                    ] : null,
                ];
            }),
            'date' => now()->format('Y-m-d'),
            'count' => $personnel->count(),
        ]);
    }

    /**
     * Rechercher par numéro de téléphone
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function parTelephone(Request $request): JsonResponse
    {
        $request->validate([
            'telephone' => 'required|string|min:4',
        ]);

        $telephone = preg_replace('/[^0-9]/', '', $request->telephone);

        $personnel = Personnel::active()
            ->where(function ($query) use ($telephone) {
                $query->where('phone_mobile', 'like', "%{$telephone}%")
                      ->orWhere('phone_office', 'like', "%{$telephone}%")
                      ->orWhere('phone_pager', 'like', "%{$telephone}%")
                      ->orWhere('extension', 'like', "%{$telephone}%");
            })
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $personnel->map(function ($p) {
                return [
                    'id' => $p->id,
                    'nom_complet' => $p->full_name,
                    'fonction' => $p->job_title,
                    'service' => $p->department,
                    'telephone_mobile' => $p->phone_mobile,
                    'telephone_fixe' => $p->phone_office,
                    'extension' => $p->extension,
                    'url' => route('annuaire.show', $p),
                ];
            }),
            'count' => $personnel->count(),
        ]);
    }

    /**
     * Rechercher par service
     * 
     * @param string $service
     * @return JsonResponse
     */
    public function parService(string $service): JsonResponse
    {
        $personnel = Personnel::active()
            ->where('department', $service)
            ->orderBy('job_title')
            ->orderBy('last_name')
            ->get();

        $stats = $this->personnelService->getStatistiquesService($service);

        return response()->json([
            'success' => true,
            'service' => $service,
            'data' => $personnel->map(function ($p) {
                return [
                    'id' => $p->id,
                    'nom_complet' => $p->full_name,
                    'fonction' => $p->job_title,
                    'specialite' => $p->specialty,
                    'telephone' => $p->primary_phone,
                    'photo' => $p->photo_url ?? '/images/default-avatar.png',
                ];
            }),
            'statistiques' => $stats,
            'count' => $personnel->count(),
        ]);
    }
}