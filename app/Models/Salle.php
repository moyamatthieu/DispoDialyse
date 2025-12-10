<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle Salle - Salles de dialyse
 * 
 * @property int $id
 * @property string $name
 * @property string|null $code
 * @property string|null $floor
 * @property string|null $building
 * @property int $capacity
 * @property bool $is_isolation
 * @property array|null $equipment
 * @property bool $is_active
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Salle extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nom de la table
     */
    protected $table = 'salles';

    /**
     * Attributs mass assignable
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'floor',
        'building',
        'capacity',
        'is_isolation',
        'equipment',
        'is_active',
        'notes',
    ];

    /**
     * Conversion de type des attributs
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_isolation' => 'boolean',
            'is_active' => 'boolean',
            'equipment' => 'array',
            'capacity' => 'integer',
        ];
    }

    /**
     * Relation avec les réservations
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Vérifier si la salle est disponible à une période donnée
     */
    public function isAvailable(\DateTime $startTime, \DateTime $endTime, ?int $excludeReservationId = null): bool
    {
        $query = $this->reservations()
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function ($q2) use ($startTime, $endTime) {
                      $q2->where('start_time', '<=', $startTime)
                         ->where('end_time', '>=', $endTime);
                  });
            })
            ->whereNotIn('status', ['cancelled', 'no_show']);

        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }

        return !$query->exists();
    }

    /**
     * Obtenir les réservations du jour
     */
    public function todayReservations()
    {
        return $this->reservations()
            ->whereDate('start_time', today())
            ->orderBy('start_time');
    }

    /**
     * Obtenir le taux d'occupation sur une période
     */
    public function occupancyRate(\DateTime $startDate, \DateTime $endDate): float
    {
        $totalHours = $startDate->diff($endDate)->days * 24;
        
        $reservedHours = $this->reservations()
            ->whereBetween('start_time', [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->get()
            ->sum(function ($reservation) {
                return $reservation->start_time->diffInHours($reservation->end_time);
            });

        return $totalHours > 0 ? ($reservedHours / $totalHours) * 100 : 0;
    }

    /**
     * Scope: Salles actives uniquement
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Salles d'isolement
     */
    public function scopeIsolation($query)
    {
        return $query->where('is_isolation', true);
    }

    /**
     * Scope: Salles standard (non isolement)
     */
    public function scopeStandard($query)
    {
        return $query->where('is_isolation', false);
    }

    /**
     * Scope: Filtrer par bâtiment
     */
    public function scopeBuilding($query, string $building)
    {
        return $query->where('building', $building);
    }

    /**
     * Scope: Filtrer par étage
     */
    public function scopeFloor($query, string $floor)
    {
        return $query->where('floor', $floor);
    }

    /**
     * Scope: Recherche par nom ou code
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%");
        });
    }
}