<?php

namespace App\Events;

use App\Models\Reservation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Événement déclenché lors de l'annulation d'une réservation
 */
class ReservationCancelled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Reservation $reservation;

    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation->load(['salle']);
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
        return 'reservation.cancelled';
    }

    public function broadcastWith(): array
    {
        return [
            'reservation' => [
                'id' => $this->reservation->id,
                'salle_id' => $this->reservation->salle_id,
                'patient_reference' => $this->reservation->patient_reference,
                'cancelled_at' => $this->reservation->cancelled_at?->toIso8601String(),
                'cancellation_reason' => $this->reservation->cancellation_reason,
            ],
            'message' => 'Réservation annulée',
        ];
    }
}