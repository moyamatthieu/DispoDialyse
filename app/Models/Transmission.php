<?php

namespace App\Models;

use App\Enums\CategorieTransmissionEnum;
use App\Enums\PrioriteTransmissionEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle Transmission - Transmissions d'informations patients
 * 
 * @property int $id
 * @property int|null $reservation_id
 * @property string $patient_reference
 * @property CategorieTransmissionEnum $category
 * @property PrioriteTransmissionEnum $priority
 * @property string $title
 * @property string $content
 * @property array|null $vital_signs
 * @property bool $has_alert
 * @property bool $alert_acknowledged
 * @property int|null $alert_acknowledged_by
 * @property \Illuminate\Support\Carbon|null $alert_acknowledged_at
 * @property int $created_by
 * @property bool $is_archived
 * @property \Illuminate\Support\Carbon|null $archived_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Transmission extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Attributs mass assignable
     */
    protected $fillable = [
        'reservation_id',
        'patient_reference',
        'category',
        'priority',
        'title',
        'content',
        'vital_signs',
        'has_alert',
        'alert_acknowledged',
        'alert_acknowledged_by',
        'alert_acknowledged_at',
        'created_by',
        'is_archived',
        'archived_at',
    ];

    /**
     * Conversion de type des attributs
     */
    protected function casts(): array
    {
        return [
            'category' => CategorieTransmissionEnum::class,
            'priority' => PrioriteTransmissionEnum::class,
            'vital_signs' => 'array',
            'has_alert' => 'boolean',
            'alert_acknowledged' => 'boolean',
            'is_archived' => 'boolean',
            'alert_acknowledged_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    /**
     * Relation avec la réservation
     */
    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    /**
     * Relation avec l'auteur
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relation avec l'utilisateur ayant accusé réception de l'alerte
     */
    public function alertAcknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'alert_acknowledged_by');
    }

    /**
     * Accuser réception de l'alerte
     */
    public function acknowledgeAlert(User $user): bool
    {
        if (!$this->has_alert || $this->alert_acknowledged) {
            return false;
        }

        return $this->update([
            'alert_acknowledged' => true,
            'alert_acknowledged_by' => $user->id,
            'alert_acknowledged_at' => now(),
        ]);
    }

    /**
     * Archiver la transmission
     */
    public function archive(): bool
    {
        return $this->update([
            'is_archived' => true,
            'archived_at' => now(),
        ]);
    }

    /**
     * Scope: Transmissions actives (non archivées)
     */
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    /**
     * Scope: Transmissions avec alertes non accusées
     */
    public function scopeUnacknowledgedAlerts($query)
    {
        return $query->where('has_alert', true)
            ->where('alert_acknowledged', false);
    }

    /**
     * Scope: Par patient
     */
    public function scopePatient($query, string $patientReference)
    {
        return $query->where('patient_reference', $patientReference);
    }

    /**
     * Scope: Par priorité
     */
    public function scopePriority($query, PrioriteTransmissionEnum $priority)
    {
        return $query->where('priority', $priority->value);
    }

    /**
     * Scope: Par catégorie
     */
    public function scopeCategory($query, CategorieTransmissionEnum $category)
    {
        return $query->where('category', $category->value);
    }
}