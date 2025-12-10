<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mailer par défaut
    |--------------------------------------------------------------------------
    |
    | Cette option contrôle le mailer par défaut qui est utilisé pour envoyer
    | tous les messages e-mail sauf si un autre mailer est explicitement
    | spécifié lors de l'envoi du message. Tous les mailers supplémentaires
    | peuvent être configurés dans le tableau "mailers" ci-dessous.
    |
    */

    'default' => env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Configurations de mailer
    |--------------------------------------------------------------------------
    |
    | Ici vous pouvez configurer tous les mailers utilisés par votre application
    | ainsi que leurs paramètres respectifs. Plusieurs exemples ont été
    | configurés pour vous et vous êtes libre d'ajouter les vôtres selon les
    | besoins de votre application.
    |
    | Laravel prend en charge une variété de pilotes de "transport" de courrier
    | à utiliser lors de l'envoi d'un e-mail. Vous pouvez spécifier lequel
    | vous utilisez pour vos mailers ci-dessous.
    |
    | Pris en charge : "smtp", "sendmail", "mailgun", "ses", "ses-v2",
    |            "postmark", "resend", "log", "array",
    |            "failover", "roundrobin"
    |
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
            // 'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        'resend' => [
            'transport' => 'resend',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Adresse "From" globale
    |--------------------------------------------------------------------------
    |
    | Vous pouvez souhaiter que tous les e-mails envoyés par votre application
    | soient envoyés depuis la même adresse. Ici, vous pouvez spécifier un nom
    | et une adresse qui sont utilisés globalement pour tous les e-mails qui
    | sont envoyés par votre application.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@dispodialyse.fr'),
        'name' => env('MAIL_FROM_NAME', 'DispoDialyse'),
    ],

];