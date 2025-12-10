<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Services tiers
    |--------------------------------------------------------------------------
    |
    | Ce fichier est pour stocker les informations d'identification pour les
    | services tiers tels que Mailgun, Postmark, AWS et plus. Ce fichier
    | fournit l'emplacement de facto pour ce type d'information, permettant
    | aux packages d'avoir un fichier conventionnel pour localiser les
    | différentes informations d'identification de service.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];