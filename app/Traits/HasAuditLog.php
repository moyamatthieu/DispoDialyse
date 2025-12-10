<?php

namespace App\Traits;

use App\Models\AuditLog;

/**
 * Trait pour activer les logs d'audit automatiques sur les modèles
 * 
 * Les actions CRUD sont automatiquement enregistrées dans la table audit_logs
 */
trait HasAuditLog
{
    /**
     * Boot du trait
     */
    protected static function bootHasAuditLog(): void
    {
        // Log lors de la création
        static::created(function ($model) {
            $model->logAudit('created', null, $model->getAuditableAttributes());
        });

        // Log lors de la mise à jour
        static::updated(function ($model) {
            $model->logAudit('updated', $model->getOriginal(), $model->getAuditableAttributes());
        });

        // Log lors de la suppression
        static::deleted(function ($model) {
            $model->logAudit('deleted', $model->getAuditableAttributes(), null);
        });
    }

    /**
     * Créer un log d'audit
     */
    protected function logAudit(string $action, ?array $oldValues, ?array $newValues): void
    {
        if (!auth()->check()) {
            return;
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => get_class($this),
            'auditable_id' => $this->id,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Obtenir les attributs à auditer
     */
    protected function getAuditableAttributes(): array
    {
        // Par défaut, tous les attributs sauf les timestamps et les champs sensibles
        $excluded = ['password', 'remember_token', 'mfa_secret', 'created_at', 'updated_at'];
        
        return collect($this->getAttributes())
            ->except($excluded)
            ->toArray();
    }

    /**
     * Relation avec les logs d'audit
     */
    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
}