<?php

namespace App\Events;

use App\Models\Reservation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Événement déclenché lors de la modification d'une réservation
 */
class ReservationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Reservation $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation->load(['salle', 'personnel']);
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('planning'),
            new Channel('planning.salle.' . $this->reservation->salle_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'reservation.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'reservation' => [
                'id' => $this->reservation->id,
                'salle_id' => $this->reservation->salle_id,
                'salle_nom' => $this->reservation->salle->name,
                'patient_reference' => $this->reservation->patient_reference,
                'type_dialyse' => $this->reservation->dialysis_type->value,
                'status' => $this->reservation->status->value,
                'start_time' => $this->reservation->start_time->toIso8601String(),
                'end_time' => $this->reservation->end_time->toIso8601String(),
            ],
            'message' => 'Réservation modifiée',
        ];
    }
}