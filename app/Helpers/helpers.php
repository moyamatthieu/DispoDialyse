<?php

use Illuminate\Support\Str;

if (! function_exists('format_date')) {
    /**
     * Formater une date en français.
     */
    function format_date($date, string $format = 'd/m/Y'): string
    {
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }

        return $date?->format($format) ?? '';
    }
}

if (! function_exists('format_datetime')) {
    /**
     * Formater une date et heure en français.
     */
    function format_datetime($date, string $format = 'd/m/Y H:i'): string
    {
        return format_date($date, $format);
    }
}

if (! function_exists('format_phone')) {
    /**
     * Formater un numéro de téléphone français.
     */
    function format_phone(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($phone) === 10) {
            return substr($phone, 0, 2) . ' ' .
                   substr($phone, 2, 2) . ' ' .
                   substr($phone, 4, 2) . ' ' .
                   substr($phone, 6, 2) . ' ' .
                   substr($phone, 8, 2);
        }

        return $phone;
    }
}

if (! function_exists('get_role_name')) {
    /**
     * Obtenir le nom du rôle en français.
     */
    function get_role_name(string $role): string
    {
        return match ($role) {
            'admin' => 'Administrateur',
            'cadre_sante' => 'Cadre de Santé',
            'infirmier' => 'Infirmier(ère)',
            'medecin' => 'Médecin',
            'aide_soignant' => 'Aide-Soignant(e)',
            default => ucfirst($role),
        };
    }
}

if (! function_exists('get_badge_color')) {
    /**
     * Obtenir la couleur du badge selon le statut.
     */
    function get_badge_color(string $status): string
    {
        return match ($status) {
            'confirmee', 'present' => 'success',
            'annulee', 'absent' => 'danger',
            'en_attente' => 'warning',
            'en_cours' => 'info',
            default => 'secondary',
        };
    }
}

if (! function_exists('get_priority_badge')) {
    /**
     * Obtenir le badge de priorité.
     */
    function get_priority_badge(string $priority): array
    {
        return match ($priority) {
            'haute' => ['color' => 'danger', 'label' => 'Haute'],
            'normale' => ['color' => 'info', 'label' => 'Normale'],
            'basse' => ['color' => 'secondary', 'label' => 'Basse'],
            default => ['color' => 'secondary', 'label' => ucfirst($priority)],
        };
    }
}

if (! function_exists('truncate')) {
    /**
     * Tronquer un texte avec des points de suspension.
     */
    function truncate(?string $text, int $length = 100, string $end = '...'): string
    {
        return Str::limit($text ?? '', $length, $end);
    }
}

if (! function_exists('active_route')) {
    /**
     * Vérifier si une route est active.
     */
    function active_route(string|array $routes, string $activeClass = 'active'): string
    {
        $routes = is_array($routes) ? $routes : [$routes];

        foreach ($routes as $route) {
            if (request()->routeIs($route)) {
                return $activeClass;
            }
        }

        return '';
    }
}

if (! function_exists('can_access')) {
    /**
     * Vérifier si l'utilisateur peut accéder à une ressource.
     */
    function can_access(string $ability, $model = null): bool
    {
        return auth()->check() && auth()->user()->can($ability, $model);
    }
}

if (! function_exists('has_role')) {
    /**
     * Vérifier si l'utilisateur a un rôle.
     */
    function has_role(string|array $roles): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $roles = is_array($roles) ? $roles : [$roles];

        return in_array(auth()->user()->role, $roles);
    }
}

if (! function_exists('flash_success')) {
    /**
     * Ajouter un message flash de succès.
     */
    function flash_success(string $message): void
    {
        session()->flash('success', $message);
    }
}

if (! function_exists('flash_error')) {
    /**
     * Ajouter un message flash d'erreur.
     */
    function flash_error(string $message): void
    {
        session()->flash('error', $message);
    }
}

if (! function_exists('flash_warning')) {
    /**
     * Ajouter un message flash d'avertissement.
     */
    function flash_warning(string $message): void
    {
        session()->flash('warning', $message);
    }
}

if (! function_exists('flash_info')) {
    /**
     * Ajouter un message flash d'information.
     */
    function flash_info(string $message): void
    {
        session()->flash('info', $message);
    }
}