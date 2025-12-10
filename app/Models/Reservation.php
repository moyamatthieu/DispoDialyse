<?php

namespace App\Models;

use App\Enums\StatutReservationEnum;
use App\Enums\TypeDialyseEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle Reservation - Séances de dialyse planifiées (cœur du système)
 * 
 * @property int $id
 * @property int $salle_id
 * @property \Illuminate\Support\Carbon $start_time
 * @property \Illuminate\Support\Carbon $end_time
 * @property string|null $patient_reference
 * @property string|null $patient_initials
 * @property TypeDialyseEnum $dialysis_type
 * @property StatutReservationEnum $status
 * @property string|null $notes
 * @property string|null $special_requirements
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $cancelled_at
 * @property string|null $cancellation_reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nom de la table
     */
    protected $table = 'reservations';

    /**
     * Attributs mass assignable
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'salle_id',
        'start_time',
        'end_time',
        'patient_reference',
        'patient_initials',
        'dialysis_type',
        'status',
        'notes',
        'special_requirements',
        'created_by',
        'cancelled_at',
        'cancellation_reason',
    ];

    /**
     * Conversion de type des attributs
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'cancelled_at' => 'datetime',
            'dialysis_type' => TypeDialyseEnum::class,
            'status' => StatutReservationEnum::class,
        ];
    }

    /**
     * Relation avec la salle
     */
    public function salle(): BelongsTo
    {
        return $this->belongsTo(Salle::class);
    }

    /**
     * Relation avec le personnel assigné (many-to-many)
     */
    public function personnel(): BelongsToMany
    {
        return $this->belongsToMany(Personnel::class, 'personnel_reservation')
            ->withPivot('role_in_session')
            ->withTimestamps();
    }

    /**
     * Relation avec l'utilisateur créateur
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation avec les transmissions
     */
    public function transmissions(): HasMany
    {
        return $this->hasMany(Transmission::class);
    }

    /**
     * Obtenir la durée de la séance en minutes
     */
    public function getDurationInMinutesAttribute(): int
    {
        return $this->start_time->diffInMinutes($this->end_time);
    }

    /**
     * Obtenir la durée de la séance en format lisible
     */
    public function getDurationFormattedAttribute(): string
    {
        $hours = floor($this->duration_in_minutes / 60);
        $minutes = $this->duration_in_minutes % 60;
        
        if ($minutes > 0) {
            return "{$hours}h{$minutes}min";
        }
        
        return "{$hours}h";
    }

    /**
     * Vérifier si la réservation est aujourd'hui
     */
    public function isToday(): bool
    {
        return $this->start_time->isToday();
    }

    /**
     * Vérifier si la réservation est en cours
     */
    public function isInProgress(): bool
    {
        return $this->status === StatutReservationEnum::IN_PROGRESS
            && $this->start_time->isPast()
            && $this->end_time->isFuture();
    }

    /**
     * Vérifier si la réservation peut être modifiée
     */
    public function isEditable(): bool
    {
        return $this->status->isEditable() && $this->start_time->isFuture();
    }

    /**
     * Vérifier si la réservation peut être annulée
     */
    public function isCancellable(): bool
    {
        return in_array($this->status, [
            StatutReservationEnum::SCHEDULED,
            StatutReservationEnum::IN_PROGRESS
        ]);
    }

    /**
     * Annuler la réservation
     */
    public function cancel(string $reason, User $user): bool
    {
        if (!$this->isCancellable()) {
            return false;
        }

        return $this->update([
            'status' => StatutReservationEnum::CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    /**
     * Démarrer la séance
     */
    public function start(): bool
    {
        if ($this->status !== StatutReservationEnum::SCHEDULED) {
            return false;
        }

        return $this->update(['status' => StatutReservationEnum::IN_PROGRESS]);
    }

    /**
     * Terminer la séance
     */
    public function complete(): bool
    {
        if (!in_array($this->status, [StatutReservationEnum::SCHEDULED, StatutReservationEnum::IN_PROGRESS])) {
            return false;
        }

        return $this->update(['status' => StatutReservationEnum::COMPLETED]);
    }

    /**
     * Scope: Réservations d'aujourd'hui
     */
    public function scopeToday($query)
    {
        return $query->whereDate('start_time', today());
    }

    /**
     * Scope: Réservations de la semaine
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('start_time', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    /**
     * Scope: Réservations à venir
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now())
            ->whereIn('status', [StatutReservationEnum::SCHEDULED, StatutReservationEnum::IN_PROGRESS]);
    }

    /**
     * Scope: Réservations par statut
     */
    public function scopeStatus($query, StatutReservationEnum $status)
    {
        return $query->where('status', $status->value);
    }

    /**
     * Scope: Réservations par type de dialyse
     */
    public function scopeDialysisType($query, TypeDialyseEnum $type)
    {
        return $query->where('dialysis_type', $type->value);
    }

    /**
     * Scope: Réservations par patient
     */
    public function scopePatient($query, string $patientReference)
    {
        return $query->where('patient_reference', $patientReference);
    }

    /**
     * Scope: Réservations par salle
     */
    public function scopeSalle($query, int $salleId)
    {
        return $query->where('salle_id', $salleId);
    }

    /**
     * Scope: Réservations entre deux dates
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_time', [$startDate, $endDate]);
    }
}