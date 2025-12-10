<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Déterminer si l'application est en mode maintenance...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Enregistrer le Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Démarrer l'application...
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);