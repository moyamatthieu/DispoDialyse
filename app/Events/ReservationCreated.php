<?php

namespace App\Events;

use App\Models\Reservation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Événement déclenché lors de la création d'une réservation
 * 
 * Broadcaster via WebSocket pour mise à jour temps réel
 */
class ReservationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Réservation créée
     */
    public Reservation $reservation;

    /**
     * Créer une nouvelle instance
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation->load(['salle', 'personnel']);
    }

    /**
     * Canaux de diffusion
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('planning'),
            new Channel('planning.salle.' . $this->reservation->salle_id),
        ];
    }

    /**
     * Nom de l'événement diffusé
     */
    public function broadcastAs(): string
    {
        return 'reservation.created';
    }

    /**
     * Données à diffuser
     */
    public function broadcastWith(): array
    {
        return [
            'reservation' => [
                'id' => $this->reservation->id,
                'salle_id' => $this->reservation->salle_id,
                'salle_nom' => $this->reservation->salle->name,
                'patient_reference' => $this->reservation->patient_reference,
                'patient_initials' => $this->reservation->patient_initials,
                'type_dialyse' => $this->reservation->dialysis_type->value,
                'status' => $this->reservation->status->value,
                'start_time' => $this->reservation->start_time->toIso8601String(),
                'end_time' => $this->reservation->end_time->toIso8601String(),
                'personnel' => $this->reservation->personnel->map(fn($p) => [
                    'id' => $p->id,
                    'nom' => $p->full_name,
                ])->toArray(),
            ],
            'message' => 'Nouvelle réservation créée',
        ];
    }
}