<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Défauts d'authentification
    |--------------------------------------------------------------------------
    |
    | Cette option contrôle le "guard" d'authentification par défaut et les
    | options de réinitialisation de mot de passe pour votre application.
    | Vous pouvez changer ces défauts selon les besoins, mais ils constituent
    | un excellent point de départ pour la plupart des applications.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Guards d'authentification
    |--------------------------------------------------------------------------
    |
    | Ensuite, vous pouvez définir chaque guard d'authentification pour votre
    | application. Bien sûr, une excellente configuration par défaut a été
    | définie pour vous ici qui utilise le stockage de session et le provider
    | d'utilisateurs Eloquent.
    |
    | Tous les guards d'authentification ont un provider d'utilisateurs. Ceci
    | définit comment les utilisateurs sont réellement récupérés de votre base
    | de données ou d'autres mécanismes de stockage utilisés par cette application
    | pour persister les données de vos utilisateurs.
    |
    | Pris en charge : "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'sanctum',
            'provider' => 'users',
            'hash' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Providers d'utilisateurs
    |--------------------------------------------------------------------------
    |
    | Tous les guards d'authentification ont un provider d'utilisateurs. Ceci
    | définit comment les utilisateurs sont réellement récupérés de votre base
    | de données ou d'autres mécanismes de stockage utilisés par cette application
    | pour persister les données de vos utilisateurs.
    |
    | Si vous avez plusieurs tables ou modèles d'utilisateurs, vous pouvez
    | configurer plusieurs sources qui représentent chaque modèle / table.
    | Ces sources peuvent ensuite être assignées à tous les guards
    | d'authentification supplémentaires que vous avez définis.
    |
    | Pris en charge : "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', App\Models\User::class),
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Réinitialisation des mots de passe
    |--------------------------------------------------------------------------
    |
    | Ces options de configuration spécifient le comportement des fonctionnalités
    | de réinitialisation de mot de passe de Laravel, y compris la table utilisée
    | pour le stockage des jetons et le provider d'utilisateurs qui est invoqué
    | pour récupérer réellement les utilisateurs.
    |
    | Le temps d'expiration est le nombre de minutes pendant lesquelles chaque
    | jeton de réinitialisation sera considéré comme valide. Cette fonctionnalité
    | de sécurité garde les jetons de courte durée de vie afin qu'ils aient
    | moins de temps pour être devinés. Vous pouvez changer cela selon les besoins.
    |
    | La restriction de throttle est le nombre de secondes qu'un utilisateur
    | doit attendre avant de générer plus de jetons de réinitialisation de mot
    | de passe. Cela permet d'empêcher l'utilisateur de générer rapidement une
    | très grande quantité de jetons de réinitialisation de mot de passe.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Délai de confirmation du mot de passe
    |--------------------------------------------------------------------------
    |
    | Ici vous pouvez définir le temps (en secondes) avant qu'une confirmation
    | de mot de passe expire et que l'utilisateur soit invité à entrer à nouveau
    | son mot de passe via l'écran de confirmation. Par défaut, le délai dure
    | trois heures.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];