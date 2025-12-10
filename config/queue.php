<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Connexion de file d'attente par défaut
    |--------------------------------------------------------------------------
    |
    | Le système de file d'attente de Laravel prend en charge une variété de
    | backends via une seule API, vous donnant un accès pratique à chacun
    | d'entre eux via le même code. Ici, vous pouvez définir une connexion
    | par défaut.
    |
    */

    'default' => env('QUEUE_CONNECTION', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Connexions de file d'attente
    |--------------------------------------------------------------------------
    |
    | Ici, vous pouvez configurer les informations de connexion pour chaque
    | serveur de file d'attente utilisé par votre application. Un exemple de
    | configuration a été ajouté pour chacun des backends de file d'attente
    | pris en charge par Laravel. Vous êtes libre d'ajouter plus.
    |
    | Pilotes : "sync", "database", "beanstalkd", "sqs", "redis", "null"
    |
    */

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'connection' => env('DB_QUEUE_CONNECTION'),
            'table' => env('DB_QUEUE_TABLE', 'jobs'),
            'queue' => env('DB_QUEUE', 'default'),
            'retry_after' => (int) env('DB_QUEUE_RETRY_AFTER', 90),
            'after_commit' => false,
        ],

        'beanstalkd' => [
            'driver' => 'beanstalkd',
            'host' => env('BEANSTALKD_QUEUE_HOST', 'localhost'),
            'queue' => env('BEANSTALKD_QUEUE', 'default'),
            'retry_after' => (int) env('BEANSTALKD_QUEUE_RETRY_AFTER', 90),
            'block_for' => 0,
            'after_commit' => false,
        ],

        'sqs' => [
            'driver' => 'sqs',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'prefix' => env('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
            'queue' => env('SQS_QUEUE', 'default'),
            'suffix' => env('SQS_SUFFIX'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'after_commit' => false,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => env('REDIS_QUEUE_CONNECTION', 'default'),
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => (int) env('REDIS_QUEUE_RETRY_AFTER', 90),
            'block_for' => null,
            'after_commit' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Traitement par lots
    |--------------------------------------------------------------------------
    |
    | Les options suivantes configurent la base de données et la table qui
    | stockent les informations de traitement par lots. Ces options peuvent
    | être mises à jour selon les besoins de la base de données que vous
    | utilisez.
    |
    */

    'batching' => [
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'job_batches',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tâches échouées
    |--------------------------------------------------------------------------
    |
    | Ces options configurent le comportement du logging des tâches échouées
    | afin que vous puissiez contrôler quelle base de données et table sont
    | utilisées pour stocker les tâches qui ont échoué. Vous pouvez les
    | changer selon vos besoins.
    |
    */

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'failed_jobs',
    ],

];