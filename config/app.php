<?php

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | Nom de l'application
    |--------------------------------------------------------------------------
    |
    | Cette valeur est le nom de votre application, qui sera utilisé lorsque
    | le framework doit afficher le nom de l'application dans une notification
    | ou tout autre emplacement requis par l'application ou ses packages.
    |
    */

    'name' => env('APP_NAME', 'DispoDialyse'),

    /*
    |--------------------------------------------------------------------------
    | Environnement de l'application
    |--------------------------------------------------------------------------
    |
    | Cette valeur détermine l'"environnement" dans lequel votre application
    | s'exécute actuellement. Cela peut déterminer comment vous préférez
    | configurer divers services que l'application utilise.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Mode de débogage de l'application
    |--------------------------------------------------------------------------
    |
    | Lorsque votre application est en mode débogage, des messages d'erreur
    | détaillés avec des traces de pile seront affichés à chaque erreur qui
    | se produit dans votre application. Si désactivé, une page d'erreur
    | générique simple sera affichée.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | URL de l'application
    |--------------------------------------------------------------------------
    |
    | Cette URL est utilisée par la console pour générer correctement les URL
    | lors de l'utilisation de l'outil de ligne de commande Artisan. Vous devez
    | définir ceci à la racine de votre application afin qu'il soit disponible
    | dans les commandes Artisan.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL'),

    /*
    |--------------------------------------------------------------------------
    | Fuseau horaire de l'application
    |--------------------------------------------------------------------------
    |
    | Ici, vous pouvez spécifier le fuseau horaire par défaut pour votre
    | application, qui sera utilisé par les fonctions de date et d'heure PHP.
    | Nous avons défini ce paramètre par défaut sur "UTC" pour vous, mais bien
    | sûr vous êtes libre de le changer selon vos besoins.
    |
    */

    'timezone' => env('APP_TIMEZONE', 'Europe/Paris'),

    /*
    |--------------------------------------------------------------------------
    | Configuration de la locale de l'application
    |--------------------------------------------------------------------------
    |
    | La locale de l'application détermine la locale par défaut qui sera utilisée
    | par le fournisseur de services de traduction. Vous êtes libre de définir
    | cette valeur à n'importe laquelle des locales qui seront prises en charge
    | par l'application.
    |
    */

    'locale' => env('APP_LOCALE', 'fr'),

    /*
    |--------------------------------------------------------------------------
    | Locale de secours de l'application
    |--------------------------------------------------------------------------
    |
    | La locale de secours détermine la locale à utiliser lorsque la locale
    | actuelle n'est pas disponible. Vous pouvez changer la valeur pour
    | correspondre à l'une des dossiers de langue fournis par votre application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Locale du faker
    |--------------------------------------------------------------------------
    |
    | Cette locale sera utilisée par la bibliothèque Faker PHP lors de la
    | génération de données factices pour vos seeds de base de données. Par
    | exemple, cela sera utilisé pour obtenir des numéros de téléphone
    | localisés, des adresses et plus encore.
    |
    */

    'faker_locale' => env('FAKER_LOCALE', 'fr_FR'),

    /*
    |--------------------------------------------------------------------------
    | Clé de chiffrement
    |--------------------------------------------------------------------------
    |
    | Cette clé est utilisée par le service de chiffrement Illuminate et doit
    | être définie sur une chaîne aléatoire de 32 caractères, sinon ces
    | chaînes chiffrées ne seront pas sûres. Veuillez faire cela avant de
    | déployer une application !
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Pilote du mode de maintenance
    |--------------------------------------------------------------------------
    |
    | Ces options de configuration déterminent le pilote utilisé pour déterminer
    | et gérer l'état du "mode de maintenance" de Laravel. Le pilote "cache"
    | permettra au mode de maintenance d'être contrôlé sur plusieurs machines.
    |
    | Pilotes pris en charge : "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fournisseurs de services chargés automatiquement
    |--------------------------------------------------------------------------
    |
    | Les fournisseurs de services listés ici seront automatiquement chargés
    | lors de la requête à votre application. N'hésitez pas à ajouter vos
    | propres services à ce tableau pour accorder des fonctionnalités étendues
    | à vos applications.
    |
    */

    'providers' => ServiceProvider::defaultProviders()->merge([
        /*
         * Fournisseurs de services de packages...
         */

        /*
         * Fournisseurs de services de l'application...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        App\Providers\BladeServiceProvider::class,
    ])->toArray(),

    /*
    |--------------------------------------------------------------------------
    | Alias de classe
    |--------------------------------------------------------------------------
    |
    | Ce tableau d'alias de classe sera enregistré lorsque cette application
    | démarre. Cependant, n'hésitez pas à enregistrer autant que vous le
    | souhaitez car les alias sont chargés "paresseusement", ils n'entravent
    | pas les performances.
    |
    */

    'aliases' => Facade::defaultAliases()->merge([
        // 'Example' => App\Facades\Example::class,
    ])->toArray(),

];