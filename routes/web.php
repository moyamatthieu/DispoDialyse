<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\SalleController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\TransmissionController;
use App\Http\Controllers\GardeController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\MessageController;

/*
|--------------------------------------------------------------------------
| Routes Web - DispoDialyse
|--------------------------------------------------------------------------
|
| Toutes les routes de l'application sont définies ici.
| Les routes sont protégées par le middleware 'auth' (authentification requise).
|
*/

// Page d'accueil publique
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
});

// Routes authentification (générées par Laravel Breeze)
require __DIR__.'/auth.php';

// Routes protégées par authentification et email vérifié
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard principal (accessible à tous les utilisateurs authentifiés)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Module Planning des Salles - Protégé par permissions
    Route::prefix('planning')->name('planning.')->group(function () {
        Route::get('/', [PlanningController::class, 'index'])->name('index')->middleware('can:planning.view');
        Route::get('/calendrier', [PlanningController::class, 'calendar'])->name('calendar')->middleware('can:planning.view');
        Route::get('/api/donnees', [PlanningController::class, 'calendarData'])->name('calendar.data')->middleware('can:planning.view');
        Route::get('/creer', [PlanningController::class, 'create'])->name('create')->middleware('can:planning.create');
        Route::post('/', [PlanningController::class, 'store'])->name('store')->middleware('can:planning.create');
        Route::get('/{reservation}', [PlanningController::class, 'show'])->name('show')->middleware('can:planning.view');
        Route::get('/{reservation}/modifier', [PlanningController::class, 'edit'])->name('edit')->middleware('can:planning.edit');
        Route::put('/{reservation}', [PlanningController::class, 'update'])->name('update')->middleware('can:planning.edit');
        Route::delete('/{reservation}', [PlanningController::class, 'destroy'])->name('destroy')->middleware('can:planning.delete');
        Route::post('/{reservation}/annuler', [PlanningController::class, 'cancel'])->name('cancel')->middleware('can:planning.edit');
    });
    
    // Module Salles de Dialyse
    Route::resource('salles', SalleController::class)->except(['show']);
    
    // Module Annuaire Personnel - Protégé par permissions
    Route::prefix('annuaire')->name('annuaire.')->middleware('can:personnel.view')->group(function () {
        Route::get('/', [PersonnelController::class, 'index'])->name('index');
        Route::get('/export', [PersonnelController::class, 'export'])->name('export');
        Route::get('/organigramme', [PersonnelController::class, 'organigramme'])->name('organigramme');
        Route::get('/trombinoscope', [PersonnelController::class, 'trombinoscope'])->name('trombinoscope');
        Route::get('/creer', [PersonnelController::class, 'create'])->name('create')->middleware('can:personnel.create');
        Route::post('/', [PersonnelController::class, 'store'])->name('store')->middleware('can:personnel.create');
        Route::get('/{personnel}', [PersonnelController::class, 'show'])->name('show');
        Route::get('/{personnel}/disponibilite', [PersonnelController::class, 'disponibilite'])->name('disponibilite');
        Route::get('/{personnel}/modifier', [PersonnelController::class, 'edit'])->name('edit');
        Route::put('/{personnel}', [PersonnelController::class, 'update'])->name('update');
        Route::delete('/{personnel}', [PersonnelController::class, 'destroy'])->name('destroy')->middleware('can:personnel.delete');
    });
    
    // Routes de recherche personnel
    Route::prefix('recherche/personnel')->name('recherche.personnel.')->middleware('can:personnel.view')->group(function () {
        Route::get('/', [\App\Http\Controllers\RecherchePersonnelController::class, 'search'])->name('search');
        Route::get('/autocomplete', [\App\Http\Controllers\RecherchePersonnelController::class, 'autocomplete'])->name('autocomplete');
        Route::get('/disponibles', [\App\Http\Controllers\RecherchePersonnelController::class, 'disponibles'])->name('disponibles');
        Route::get('/de-garde', [\App\Http\Controllers\RecherchePersonnelController::class, 'deGarde'])->name('de-garde');
        Route::get('/competence/{competence}', [\App\Http\Controllers\RecherchePersonnelController::class, 'parCompetence'])->name('par-competence');
        Route::get('/service/{service}', [\App\Http\Controllers\RecherchePersonnelController::class, 'parService'])->name('par-service');
        Route::get('/telephone', [\App\Http\Controllers\RecherchePersonnelController::class, 'parTelephone'])->name('par-telephone');
    });
    
    // Module Transmissions Patients - Protégé par permissions
    Route::prefix('transmissions')->name('transmissions.')->middleware('can:transmissions.view')->group(function () {
        Route::get('/', [TransmissionController::class, 'index'])->name('index');
        Route::get('/creer', [TransmissionController::class, 'create'])->name('create')->middleware('can:transmissions.create');
        Route::post('/', [TransmissionController::class, 'store'])->name('store')->middleware('can:transmissions.create');
        Route::get('/{transmission}', [TransmissionController::class, 'show'])->name('show');
        Route::get('/{transmission}/modifier', [TransmissionController::class, 'edit'])->name('edit')->middleware('can:transmissions.edit');
        Route::put('/{transmission}', [TransmissionController::class, 'update'])->name('update')->middleware('can:transmissions.edit');
        Route::delete('/{transmission}', [TransmissionController::class, 'destroy'])->name('destroy')->middleware('can:transmissions.delete');
        Route::post('/{transmission}/accuser-reception', [TransmissionController::class, 'acknowledge'])->name('acknowledge');
        Route::get('/patient/{reference}', [TransmissionController::class, 'byPatient'])->name('by-patient');
    });
    
    // Module Planning de Garde - Protégé par permissions
    Route::prefix('gardes')->name('gardes.')->middleware('can:gardes.view')->group(function () {
        Route::get('/', [GardeController::class, 'index'])->name('index');
        Route::get('/actuel', [GardeController::class, 'current'])->name('current');
        Route::get('/creer', [GardeController::class, 'create'])->name('create')->middleware('can:gardes.manage');
        Route::post('/', [GardeController::class, 'store'])->name('store')->middleware('can:gardes.manage');
        Route::get('/{garde}', [GardeController::class, 'show'])->name('show');
        Route::get('/{garde}/modifier', [GardeController::class, 'edit'])->name('edit')->middleware('can:gardes.manage');
        Route::put('/{garde}', [GardeController::class, 'update'])->name('update')->middleware('can:gardes.manage');
        Route::delete('/{garde}', [GardeController::class, 'destroy'])->name('destroy')->middleware('can:gardes.manage');
    });
    
    // Module Documents et Protocoles - Protégé par permissions
    Route::prefix('documents')->name('documents.')->middleware('can:documents.view')->group(function () {
        Route::get('/', [DocumentController::class, 'index'])->name('index');
        Route::get('/recherche', [DocumentController::class, 'search'])->name('search');
        Route::get('/telecharger', [DocumentController::class, 'create'])->name('create')->middleware('can:documents.upload');
        Route::post('/', [DocumentController::class, 'store'])->name('store')->middleware('can:documents.upload');
        Route::get('/{document}', [DocumentController::class, 'show'])->name('show');
        Route::get('/{document}/telecharger', [DocumentController::class, 'download'])->name('download');
        Route::get('/{document}/modifier', [DocumentController::class, 'edit'])->name('edit')->middleware('can:documents.edit');
        Route::put('/{document}', [DocumentController::class, 'update'])->name('update')->middleware('can:documents.edit');
        Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('destroy')->middleware('can:documents.delete');
    });
    
    // Module Messagerie Interne - Protégé par permissions
    Route::prefix('messages')->name('messages.')->middleware('can:messages.view')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::get('/composer', [MessageController::class, 'create'])->name('create')->middleware('can:messages.send');
        Route::post('/', [MessageController::class, 'store'])->name('store')->middleware('can:messages.send');
        Route::get('/{message}', [MessageController::class, 'show'])->name('show');
        Route::post('/{message}/marquer-lu', [MessageController::class, 'markAsRead'])->name('mark-as-read');
        Route::delete('/{message}', [MessageController::class, 'destroy'])->name('destroy');
        Route::post('/{message}/repondre', [MessageController::class, 'reply'])->name('reply')->middleware('can:messages.send');
    });
    
    // Module Salles de Dialyse - Protégé (admin uniquement)
    Route::middleware('can:settings.manage')->group(function () {
        Route::resource('salles', SalleController::class)->except(['show']);
    });
    
    // Administration - Réservé aux administrateurs
    Route::middleware('role:super_admin,admin_fonctionnel')->prefix('admin')->name('admin.')->group(function () {
        // Gestion des utilisateurs déjà dans routes/auth.php
        
        // Audit logs
        Route::get('/audit', function() {
            return view('admin.audit.index');
        })->name('audit.index')->middleware('can:audit.view');
        
        // Paramètres système
        Route::get('/settings', function() {
            return view('admin.settings.index');
        })->name('settings.index')->middleware('can:settings.manage');
    });
    
    // Routes profil utilisateur
    Route::prefix('profil')->name('profile.')->group(function () {
        Route::get('/', function () {
            return view('profile.edit');
        })->name('edit');
    });
});