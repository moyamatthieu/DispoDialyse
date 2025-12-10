<?php

namespace App\Models;

use App\Enums\RoleEnum;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * Modèle User - Comptes utilisateurs avec authentification
 * 
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password
 * @property string $first_name
 * @property string $last_name
 * @property RoleEnum $role
 * @property bool $is_active
 * @property bool $mfa_enabled
 * @property string|null $mfa_secret
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $last_login_at
 * @property string|null $last_login_ip
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * Attributs mass assignable
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'first_name',
        'last_name',
        'phone',
        'role',
        'is_active',
        'mfa_enabled',
    ];

    /**
     * Attributs à cacher dans les sérialisations
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'mfa_secret',
    ];

    /**
     * Conversion de type des attributs
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'role' => RoleEnum::class,
            'is_active' => 'boolean',
            'mfa_enabled' => 'boolean',
            'mfa_secret' => 'encrypted',
        ];
    }

    /**
     * Relation avec le profil personnel détaillé
     */
    public function personnel(): HasOne
    {
        return $this->hasOne(Personnel::class);
    }

    /**
     * Relation avec les réservations créées
     */
    public function reservationsCreated(): HasMany
    {
        return $this->hasMany(Reservation::class, 'created_by');
    }

    /**
     * Relation avec les transmissions créées
     */
    public function transmissions(): HasMany
    {
        return $this->hasMany(Transmission::class, 'created_by');
    }

    /**
     * Relation avec les messages envoyés
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Relation avec les messages reçus
     */
    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'recipient_id');
    }

    /**
     * Relation avec les documents créés
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'created_by');
    }

    /**
     * Relation avec les logs d'audit
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Obtenir le nom complet de l'utilisateur
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Obtenir les initiales de l'utilisateur
     */
    public function getInitialsAttribute(): string
    {
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
    }

    /**
     * Vérifier si l'utilisateur est administrateur
     */
    public function isAdmin(): bool
    {
        return $this->role->isAdmin();
    }

    /**
     * Vérifier si l'utilisateur est personnel médical
     */
    public function isMedical(): bool
    {
        return $this->role->isMedical();
    }

    /**
     * Vérifier si l'utilisateur peut gérer le planning
     */
    public function canManagePlanning(): bool
    {
        return $this->role->canManagePlanning();
    }

    /**
     * Vérifier si l'utilisateur peut voir les transmissions
     */
    public function canViewTransmissions(): bool
    {
        return $this->role->canViewTransmissions();
    }

    /**
     * Enregistrer la dernière connexion
     */
    public function recordLogin(string $ipAddress): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress,
        ]);
    }

    /**
     * Scope: Utilisateurs actifs uniquement
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filtrer par rôle
     */
    public function scopeRole($query, RoleEnum $role)
    {
        return $query->where('role', $role->value);
    }

    /**
     * Scope: Recherche par nom ou email
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('username', 'like', "%{$search}%");
        });
    }
}