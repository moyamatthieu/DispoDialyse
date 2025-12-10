<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Magasin de cache par défaut
    |--------------------------------------------------------------------------
    |
    | Cette option contrôle la connexion de cache par défaut qui est utilisée
    | par le framework. Cette connexion est utilisée si aucune autre n'est
    | explicitement spécifiée lors de l'exécution d'une opération de cache
    | donnée.
    |
    */

    'default' => env('CACHE_STORE', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Magasins de cache
    |--------------------------------------------------------------------------
    |
    | Ici vous pouvez définir tous les "magasins" de cache pour votre application
    | ainsi que leurs pilotes. Vous pouvez même définir plusieurs magasins pour
    | le même pilote de cache afin de regrouper les types d'éléments stockés
    | dans vos caches.
    |
    | Pilotes pris en charge : "apc", "array", "database", "file",
    |                          "memcached", "redis", "dynamodb", "octane", "null"
    |
    */

    'stores' => [

        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],

        'database' => [
            'driver' => 'database',
            'table' => env('DB_CACHE_TABLE', 'cache'),
            'connection' => env('DB_CACHE_CONNECTION'),
            'lock_connection' => env('DB_CACHE_LOCK_CONNECTION'),
        ],

        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
            'lock_path' => storage_path('framework/cache/data'),
        ],

        'memcached' => [
            'driver' => 'memcached',
            'persistent_id' => env('MEMCACHED_PERSISTENT_ID'),
            'sasl' => [
                env('MEMCACHED_USERNAME'),
                env('MEMCACHED_PASSWORD'),
            ],
            'options' => [
                // Memcached::OPT_CONNECT_TIMEOUT => 2000,
            ],
            'servers' => [
                [
                    'host' => env('MEMCACHED_HOST', '127.0.0.1'),
                    'port' => env('MEMCACHED_PORT', 11211),
                    'weight' => 100,
                ],
            ],
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
            'lock_connection' => env('REDIS_CACHE_LOCK_CONNECTION', 'default'),
        ],

        'dynamodb' => [
            'driver' => 'dynamodb',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'table' => env('DYNAMODB_CACHE_TABLE', 'cache'),
            'endpoint' => env('DYNAMODB_ENDPOINT'),
        ],

        'octane' => [
            'driver' => 'octane',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Préfixe de clé de cache
    |--------------------------------------------------------------------------
    |
    | Lors de l'utilisation des magasins APC, database, memcached, Redis et
    | DynamoDB, il peut y avoir d'autres applications utilisant le même cache.
    | Pour cette raison, vous pouvez préfixer chaque clé de cache afin d'éviter
    | les collisions.
    |
    */

    'prefix' => env('CACHE_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_cache_'),

];