<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Pilote de session par défaut
    |--------------------------------------------------------------------------
    |
    | Cette option contrôle le "pilote" de session par défaut qui sera utilisé
    | lors des requêtes. Par défaut, nous utiliserons le pilote natif léger
    | mais vous pouvez spécifier n'importe lequel des autres merveilleux
    | pilotes fournis ici.
    |
    | Pris en charge : "file", "cookie", "database", "apc",
    |            "memcached", "redis", "dynamodb", "array"
    |
    */

    'driver' => env('SESSION_DRIVER', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Durée de vie de la session
    |--------------------------------------------------------------------------
    |
    | Ici vous pouvez spécifier le nombre de minutes pendant lesquelles vous
    | souhaitez que la session reste inactive avant qu'elle n'expire. Si vous
    | voulez qu'elles expirent immédiatement à la fermeture du navigateur,
    | définissez cette option sur zéro.
    |
    */

    'lifetime' => env('SESSION_LIFETIME', 120),

    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    /*
    |--------------------------------------------------------------------------
    | Chiffrement de session
    |--------------------------------------------------------------------------
    |
    | Cette option vous permet de spécifier facilement que toutes vos données
    | de session doivent être chiffrées avant d'être stockées. Tout le
    | chiffrement sera exécuté automatiquement par Laravel et vous pouvez
    | utiliser la session comme d'habitude.
    |
    */

    'encrypt' => env('SESSION_ENCRYPT', false),

    /*
    |--------------------------------------------------------------------------
    | Emplacement de fichier de session
    |--------------------------------------------------------------------------
    |
    | Lorsque vous utilisez le pilote de session natif, nous avons besoin d'un
    | emplacement où les fichiers de session peuvent être stockés. Une valeur
    | par défaut a été définie pour vous, mais un emplacement différent peut
    | être spécifié. Ceci n'est nécessaire que pour les sessions sur fichier.
    |
    */

    'files' => storage_path('framework/sessions'),

    /*
    |--------------------------------------------------------------------------
    | Connexion à la base de données de session
    |--------------------------------------------------------------------------
    |
    | Lorsque vous utilisez les pilotes de session "database" ou "redis", vous
    | pouvez spécifier une connexion qui doit être utilisée pour gérer ces
    | sessions. Cela devrait correspondre à une connexion dans vos options de
    | configuration de base de données.
    |
    */

    'connection' => env('SESSION_CONNECTION'),

    /*
    |--------------------------------------------------------------------------
    | Table de base de données de session
    |--------------------------------------------------------------------------
    |
    | Lorsque vous utilisez le pilote de session "database", vous pouvez
    | spécifier la table que nous devons utiliser pour gérer les sessions.
    | Bien sûr, une valeur par défaut sensée est fournie pour vous, mais
    | vous êtes libre de la changer selon les besoins.
    |
    */

    'table' => env('SESSION_TABLE', 'sessions'),

    /*
    |--------------------------------------------------------------------------
    | Magasin de cache de session
    |--------------------------------------------------------------------------
    |
    | Lors de l'utilisation de l'un des backends de session orientés cache de
    | Laravel, vous pouvez lister un magasin de cache qui doit être utilisé
    | pour ces sessions. Cette valeur doit correspondre à l'un des "magasins"
    | de cache configurés de l'application.
    |
    | Affecte : "apc", "dynamodb", "memcached", "redis"
    |
    */

    'store' => env('SESSION_STORE'),

    /*
    |--------------------------------------------------------------------------
    | Loterie de balayage de session
    |--------------------------------------------------------------------------
    |
    | Certains pilotes de session doivent balayer manuellement leur emplacement
    | de stockage pour se débarrasser des anciennes sessions du stockage. Voici
    | les chances que cela se produise sur une requête donnée. Par défaut, les
    | chances sont de 2 sur 100.
    |
    */

    'lottery' => [2, 100],

    /*
    |--------------------------------------------------------------------------
    | Nom du cookie de session
    |--------------------------------------------------------------------------
    |
    | Ici vous pouvez changer le nom du cookie de session utilisé par
    | l'application. Cela ne devrait généralement pas être modifié, mais si
    | vous en avez besoin, c'est ici que vous pouvez le faire. Le nom du
    | cookie de session doit être un slug valide.
    |
    */

    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug(env('APP_NAME', 'laravel'), '_').'_session'
    ),

    /*
    |--------------------------------------------------------------------------
    | Chemin du cookie de session
    |--------------------------------------------------------------------------
    |
    | Le chemin du cookie de session détermine le chemin pour lequel le cookie
    | sera considéré comme disponible. Typiquement, ce sera le chemin racine
    | de votre application, mais vous êtes libre de changer cela lorsque
    | nécessaire.
    |
    */

    'path' => env('SESSION_PATH', '/'),

    /*
    |--------------------------------------------------------------------------
    | Domaine du cookie de session
    |--------------------------------------------------------------------------
    |
    | Ici vous pouvez changer le domaine du cookie afin de déterminer quels
    | domaines le cookie est disponible. Cette valeur par défaut rendra le
    | cookie disponible uniquement sur le domaine actuel de l'application.
    |
    */

    'domain' => env('SESSION_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Cookies sécurisés uniquement HTTPS
    |--------------------------------------------------------------------------
    |
    | En définissant cette option à true, les cookies de session ne seront
    | envoyés au serveur que si le navigateur a une connexion HTTPS. Cela
    | empêchera le cookie d'être envoyé lorsqu'il ne peut pas être fait de
    | manière sécurisée.
    |
    */

    'secure' => env('SESSION_SECURE_COOKIE', env('APP_ENV') === 'production'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Access Only
    |--------------------------------------------------------------------------
    |
    | Définir cette valeur à true empêchera JavaScript d'accéder à la valeur
    | du cookie et le cookie ne sera accessible que via le protocole HTTP.
    | Vous êtes libre de modifier cette option si nécessaire.
    |
    */

    'http_only' => env('SESSION_HTTP_ONLY', true),

    /*
    |--------------------------------------------------------------------------
    | Cookie Same-Site
    |--------------------------------------------------------------------------
    |
    | Cette option détermine comment vos cookies se comportent lorsque des
    | requêtes cross-site ont lieu, et peut être utilisée pour atténuer les
    | attaques CSRF. Par défaut, nous définirons cette valeur à "lax" car
    | c'est une valeur par défaut sécurisée.
    |
    | Pris en charge : "lax", "strict", "none", null
    |
    */

    'same_site' => env('SESSION_SAME_SITE', 'lax'),

    /*
    |--------------------------------------------------------------------------
    | Partitionnement des cookies
    |--------------------------------------------------------------------------
    |
    | Activer le partitionnement des cookies de session empêchera le cookie
    | d'être lu depuis des domaines ou des schémas différents. Cette fonctionnalité
    | de sécurité est activée par défaut mais peut être désactivée si nécessaire.
    |
    */

    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),

];