<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Transmission;
use App\Models\Garde;
use App\Models\Message;
use App\Models\Personnel;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Contrôleur du tableau de bord principal
 * 
 * Affiche des widgets différents selon le rôle de l'utilisateur
 */
class DashboardController extends Controller
{
    /**
     * Afficher le tableau de bord
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        // Données communes à tous les rôles
        $data = [
            'user' => $user,
            'unreadMessages' => Message::where('recipient_id', $user->id)
                ->where('read_at', null)
                ->count(),
        ];

        // Données spécifiques selon le rôle
        switch ($user->role->value) {
            case 'super_admin':
            case 'admin_fonctionnel':
                $data = array_merge($data, $this->getAdminData());
                break;

            case 'cadre_sante':
                $data = array_merge($data, $this->getCadreSanteData());
                break;

            case 'medecin':
            case 'infirmier':
                $data = array_merge($data, $this->getMedicalData($user));
                break;

            case 'aide_soignant':
                $data = array_merge($data, $this->getAideSoignantData());
                break;

            case 'secretariat':
                $data = array_merge($data, $this->getSecretariatData());
                break;

            case 'technicien':
                $data = array_merge($data, $this->getTechnicienData());
                break;
        }

        return view('dashboard', $data);
    }

    /**
     * Données pour les administrateurs
     */
    private function getAdminData(): array
    {
        return [
            'totalReservations' => Reservation::whereDate('start_time', today())->count(),
            'totalPersonnel' => Personnel::where('is_active', true)->count(),
            'totalTransmissions' => Transmission::whereDate('created_at', today())->count(),
            'recentActivity' => activity()->latest()->take(10)->get(),
        ];
    }

    /**
     * Données pour le cadre de santé
     */
    private function getCadreSanteData(): array
    {
        return [
            'totalPersonnel' => Personnel::where('is_active', true)->count(),
            'personnelEnConge' => Personnel::where('is_active', false)->count(),
            'reservationsAujourdhui' => Reservation::whereDate('start_time', today())->count(),
            'gardesAVenir' => Garde::where('start_datetime', '>=', now())->take(5)->get(),
        ];
    }

    /**
     * Données pour le personnel médical (médecin/infirmier)
     */
    private function getMedicalData($user): array
    {
        return [
            'reservationsAujourdhui' => Reservation::whereDate('start_time', today())
                ->with(['salle', 'personnel'])
                ->take(10)
                ->get(),
            'transmissionsUrgentes' => Transmission::where('priority', 'high')
                ->where('is_archived', false)
                ->latest()
                ->take(5)
                ->get(),
            'mesReservations' => Reservation::whereHas('personnel', function($query) use ($user) {
                $query->where('personnel.user_id', $user->id);
            })
            ->whereDate('start_time', today())
            ->get(),
        ];
    }

    /**
     * Données pour l'aide-soignant
     */
    private function getAideSoignantData(): array
    {
        return [
            'reservationsAujourdhui' => Reservation::whereDate('start_time', today())
                ->with('salle')
                ->take(10)
                ->get(),
            'transmissionsRecentes' => Transmission::latest()->take(5)->get(),
        ];
    }

    /**
     * Données pour le secrétariat
     */
    private function getSecretariatData(): array
    {
        return [
            'reservationsAVenir' => Reservation::where('start_time', '>=', today())
                ->orderBy('start_time')
                ->take(10)
                ->get(),
            'personnelActif' => Personnel::where('is_active', true)->count(),
            'tachesAdministratives' => [], // À implémenter selon besoins
        ];
    }

    /**
     * Données pour le technicien
     */
    private function getTechnicienData(): array
    {
        return [
            'reservationsAujourdhui' => Reservation::whereDate('start_time', today())
                ->with('salle')
                ->get(),
            'sallesDisponibles' => \App\Models\Salle::where('is_active', true)->count(),
        ];
    }
}