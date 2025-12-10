<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware pour logger automatiquement les requêtes sensibles
 */
class AuditLog
{
    /**
     * Routes sensibles à auditer
     */
    protected array $sensitiveRoutes = [
        'users.*',
        'personnel.*',
        'transmissions.*',
        'documents.*',
        'settings.*',
    ];

    /**
     * Gérer une requête entrante
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Logger uniquement si l'utilisateur est authentifié
        if ($request->user() && $this->shouldLog($request)) {
            $this->logRequest($request, $response);
        }

        return $response;
    }

    /**
     * Vérifier si la requête doit être loggée
     */
    protected function shouldLog(Request $request): bool
    {
        // Logger les méthodes modifiant les données
        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return false;
        }

        // Logger les routes sensibles
        $routeName = $request->route()?->getName();
        if (!$routeName) {
            return false;
        }

        foreach ($this->sensitiveRoutes as $pattern) {
            if (fnmatch($pattern, $routeName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Logger la requête
     */
    protected function logRequest(Request $request, Response $response): void
    {
        $user = $request->user();
        $routeName = $request->route()?->getName();
        $method = $request->method();
        $statusCode = $response->getStatusCode();

        // Ne logger que les requêtes réussies (2xx, 3xx)
        if ($statusCode >= 400) {
            return;
        }

        // Déterminer l'action
        $action = $this->determineAction($method, $routeName);

        // Récupérer les données pertinentes (sans les mots de passe)
        $data = $this->getSanitizedData($request);

        activity()
            ->causedBy($user)
            ->withProperties([
                'route' => $routeName,
                'method' => $method,
                'url' => $request->fullUrl(),
                'data' => $data,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status_code' => $statusCode,
            ])
            ->log($action);
    }

    /**
     * Déterminer l'action à partir de la méthode HTTP
     */
    protected function determineAction(string $method, ?string $routeName): string
    {
        return match ($method) {
            'POST' => 'Création via ' . $routeName,
            'PUT', 'PATCH' => 'Modification via ' . $routeName,
            'DELETE' => 'Suppression via ' . $routeName,
            default => 'Action via ' . $routeName,
        };
    }

    /**
     * Obtenir les données de la requête sans les champs sensibles
     */
    protected function getSanitizedData(Request $request): array
    {
        $data = $request->all();

        // Supprimer les champs sensibles
        $sensitiveFields = [
            'password',
            'password_confirmation',
            'current_password',
            'mfa_secret',
            'remember_token',
            '_token',
            '_method',
        ];

        foreach ($sensitiveFields as $field) {
            unset($data[$field]);
        }

        return $data;
    }
}