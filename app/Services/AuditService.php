<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Service centralisé pour la gestion des logs d'audit
 * 
 * Conforme aux exigences RGPD
 */
class AuditService
{
    /**
     * Créer un log d'audit manuel
     */
    public function log(
        User $user,
        string $action,
        ?string $auditableType = null,
        ?int $auditableId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $user->id,
            'action' => $action,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'description' => $description,
        ]);
    }

    /**
     * Obtenir les logs d'un utilisateur
     */
    public function getUserLogs(User $user, int $limit = 50): Collection
    {
        return AuditLog::where('user_id', $user->id)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Obtenir les logs d'une ressource
     */
    public function getResourceLogs(string $type, int $id, int $limit = 50): Collection
    {
        return AuditLog::where('auditable_type', $type)
            ->where('auditable_id', $id)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Obtenir les logs par action
     */
    public function getLogsByAction(string $action, int $limit = 100): Collection
    {
        return AuditLog::where('action', $action)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Rechercher dans les logs
     */
    public function search(array $filters): Collection
    {
        $query = AuditLog::query()->with('user');

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (isset($filters['auditable_type'])) {
            $query->where('auditable_type', $filters['auditable_type']);
        }

        if (isset($filters['ip_address'])) {
            $query->where('ip_address', $filters['ip_address']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->latest()->limit($filters['limit'] ?? 100)->get();
    }

    /**
     * Obtenir les statistiques d'audit
     */
    public function getStatistics(): array
    {
        return [
            'total_logs' => AuditLog::count(),
            'logs_today' => AuditLog::whereDate('created_at', today())->count(),
            'logs_this_week' => AuditLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'logs_this_month' => AuditLog::whereMonth('created_at', now()->month)->count(),
            'actions_count' => AuditLog::selectRaw('action, COUNT(*) as count')
                ->groupBy('action')
                ->pluck('count', 'action')
                ->toArray(),
            'most_active_users' => AuditLog::selectRaw('user_id, COUNT(*) as count')
                ->groupBy('user_id')
                ->orderByDesc('count')
                ->limit(10)
                ->with('user')
                ->get()
                ->map(fn($log) => [
                    'user' => $log->user->full_name ?? 'Utilisateur inconnu',
                    'count' => $log->count
                ])
                ->toArray(),
        ];
    }

    /**
     * Nettoyer les anciens logs (RGPD - conservation limitée)
     */
    public function cleanOldLogs(int $daysToKeep = 365): int
    {
        $date = now()->subDays($daysToKeep);
        
        return AuditLog::where('created_at', '<', $date)->delete();
    }

    /**
     * Exporter les logs d'un utilisateur (RGPD - droit d'accès)
     */
    public function exportUserLogs(User $user): array
    {
        return AuditLog::where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($log) {
                return [
                    'date' => $log->created_at->format('Y-m-d H:i:s'),
                    'action' => $log->action,
                    'ressource' => $log->auditable_type,
                    'ressource_id' => $log->auditable_id,
                    'ip' => $log->ip_address,
                    'description' => $log->description,
                ];
            })
            ->toArray();
    }

    /**
     * Anonymiser les logs d'un utilisateur (RGPD - droit à l'oubli)
     */
    public function anonymizeUserLogs(User $user): int
    {
        return AuditLog::where('user_id', $user->id)
            ->update([
                'user_id' => null,
                'ip_address' => '0.0.0.0',
                'user_agent' => 'Anonymisé (RGPD)',
                'old_values' => null,
                'new_values' => null,
            ]);
    }
}