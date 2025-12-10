<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Chemins Cross-Origin Resource Sharing (CORS)
    |--------------------------------------------------------------------------
    |
    | Ici vous pouvez configurer vos paramètres pour le partage de ressources
    | cross-origin ou "CORS". Cela détermine quelles opérations cross-origin
    | peuvent être exécutées dans les navigateurs web. Vous êtes libre d'ajuster
    | ces paramètres selon les besoins.
    |
    | Pour en savoir plus : https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:3000')],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];