<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle AuditLog - Logs d'audit pour traçabilité (RGPD/Sécurité)
 */
class AuditLog extends Model
{
    use HasFactory;

    /**
     * Pas de updated_at pour les logs
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'action',
        'entity_type',
        'entity_id',
        'description',
        'changes',
        'severity',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'entity_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Créer un log d'audit
     */
    public static function logAction(
        string $action,
        ?User $user = null,
        ?string $entityType = null,
        ?int $entityId = null,
        ?string $description = null,
        ?array $changes = null,
        string $severity = 'info'
    ): self {
        return self::create([
            'user_id' => $user?->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'description' => $description,
            'changes' => $changes,
            'severity' => $severity,
        ]);
    }

    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeEntity($query, string $entityType, ?int $entityId = null)
    {
        $query = $query->where('entity_type', $entityType);
        
        if ($entityId) {
            $query->where('entity_id', $entityId);
        }
        
        return $query;
    }

    public function scopeSeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}