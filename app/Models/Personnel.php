<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modèle Personnel - Profils détaillés du personnel (annuaire)
 * 
 * @property int $id
 * @property int|null $user_id
 * @property string $first_name
 * @property string $last_name
 * @property string|null $photo_url
 * @property string|null $phone_office
 * @property string|null $phone_mobile
 * @property string|null $phone_pager
 * @property string|null $email_pro
 * @property string|null $extension
 * @property string $job_title
 * @property string|null $specialty
 * @property string $department
 * @property string $employment_type
 * @property array|null $qualifications
 * @property array|null $languages
 * @property array|null $certifications
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $hire_date
 * @property \Illuminate\Support\Carbon|null $leave_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class Personnel extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nom de la table
     */
    protected $table = 'personnel';

    /**
     * Attributs mass assignable
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'photo_url',
        'phone_office',
        'phone_mobile',
        'phone_pager',
        'email_pro',
        'extension',
        'job_title',
        'specialty',
        'department',
        'employment_type',
        'qualifications',
        'languages',
        'certifications',
        'is_active',
        'hire_date',
        'leave_date',
    ];

    /**
     * Conversion de type des attributs
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hire_date' => 'date',
            'leave_date' => 'date',
            'is_active' => 'boolean',
            'qualifications' => 'array',
            'languages' => 'array',
            'certifications' => 'array',
        ];
    }

    /**
     * Relation avec le compte utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec les réservations (many-to-many)
     */
    public function reservations(): BelongsToMany
    {
        return $this->belongsToMany(Reservation::class, 'personnel_reservation')
            ->withPivot('role_in_session')
            ->withTimestamps();
    }

    /**
     * Relation avec les gardes
     */
    public function gardes(): HasMany
    {
        return $this->hasMany(Garde::class);
    }

    /**
     * Obtenir le nom complet
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Obtenir les initiales
     */
    public function getInitialsAttribute(): string
    {
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
    }

    /**
     * Obtenir le téléphone principal (mobile prioritaire)
     */
    public function getPrimaryPhoneAttribute(): ?string
    {
        return $this->phone_mobile ?? $this->phone_office ?? $this->phone_pager;
    }

    /**
     * Vérifier si le personnel a un compte utilisateur
     */
    public function hasUserAccount(): bool
    {
        return $this->user_id !== null;
    }

    /**
     * Vérifier si le personnel est actuellement de garde
     */
    public function isOnCall(): bool
    {
        return $this->gardes()
            ->where('start_datetime', '<=', now())
            ->where('end_datetime', '>=', now())
            ->where('status', 'confirmed')
            ->exists();
    }

    /**
     * Scope: Personnel actif uniquement
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filtrer par département
     */
    public function scopeDepartment($query, string $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Scope: Recherche par nom, fonction ou spécialité
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('job_title', 'like', "%{$search}%")
              ->orWhere('specialty', 'like', "%{$search}%")
              ->orWhere('phone_mobile', 'like', "%{$search}%")
              ->orWhere('phone_office', 'like', "%{$search}%");
        });
    }

    /**
     * Scope: Personnel avec compte utilisateur
     */
    public function scopeWithUserAccount($query)
    {
        return $query->whereNotNull('user_id');
    }

    /**
     * Scope: Personnel externe (sans compte)
     */
    public function scopeExternal($query)
    {
        return $query->whereNull('user_id');
    }
}