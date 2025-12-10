<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PlanningApiController;
use App\Http\Controllers\Api\NotificationApiController;

/*
|--------------------------------------------------------------------------
| Routes API
|--------------------------------------------------------------------------
|
| Routes API pour les appels AJAX et intégrations externes.
| Toutes les routes sont protégées par Sanctum (authentification token).
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    
    // Informations utilisateur connecté
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // API Planning
    Route::prefix('planning')->group(function () {
        Route::get('/disponibilites', [PlanningApiController::class, 'availabilities']);
        Route::get('/conflits', [PlanningApiController::class, 'checkConflicts']);
        Route::get('/statistiques', [PlanningApiController::class, 'statistics']);
    });
    
    // API Personnel
    Route::prefix('personnel')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\PersonnelApiController::class, 'index']);
        Route::get('/search', [\App\Http\Controllers\Api\PersonnelApiController::class, 'search']);
        Route::get('/autocomplete', [\App\Http\Controllers\Api\PersonnelApiController::class, 'autocomplete']);
        Route::get('/disponibles', [\App\Http\Controllers\Api\PersonnelApiController::class, 'disponibles']);
        Route::get('/de-garde', [\App\Http\Controllers\Api\PersonnelApiController::class, 'deGarde']);
        Route::get('/par-competence/{competence}', [\App\Http\Controllers\Api\PersonnelApiController::class, 'parCompetence']);
        Route::get('/organigramme', [\App\Http\Controllers\Api\PersonnelApiController::class, 'organigramme']);
        Route::get('/statistiques/{service}', [\App\Http\Controllers\Api\PersonnelApiController::class, 'statistiquesService']);
        Route::get('/{personnel}', [\App\Http\Controllers\Api\PersonnelApiController::class, 'show']);
    });
    
    // API Recherche Personnel (alias pour compatibilité)
    Route::prefix('recherche/personnel')->group(function () {
        Route::get('/', [\App\Http\Controllers\RecherchePersonnelController::class, 'search']);
        Route::get('/autocomplete', [\App\Http\Controllers\RecherchePersonnelController::class, 'autocomplete']);
    });
    
    // API Notifications
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationApiController::class, 'index']);
        Route::post('/{notification}/lire', [NotificationApiController::class, 'markAsRead']);
        Route::post('/marquer-toutes-lues', [NotificationApiController::class, 'markAllAsRead']);
        Route::delete('/{notification}', [NotificationApiController::class, 'destroy']);
    });
});