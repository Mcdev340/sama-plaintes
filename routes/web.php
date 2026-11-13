<?php

use App\Http\Controllers\AlerteController;
use App\Http\Controllers\Auth\ActivationController;
use App\Http\Controllers\Auth\ConnexionController;
use App\Http\Controllers\Auth\InscriptionController;
use App\Http\Controllers\Auth\MotDePasseController;
use App\Http\Controllers\AutoriteController;
use App\Http\Controllers\Citoyen\TableauBordController;
use App\Http\Controllers\CitoyenController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PieceJointeController;
use App\Http\Controllers\PlainteController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\RedirectionController;
use App\Http\Controllers\SuperviseurController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('accueil');
Route::get('/fonctionnalites', [HomeController::class, 'fonctionnalites'])->name('fonctionnalites');
Route::get('/a-propos', [HomeController::class, 'aPropos'])->name('a-propos');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'envoyer'])->middleware('throttle:6,1')->name('contact.envoyer');
Route::get('/langue/{locale}', [LocaleController::class, 'update'])->name('locale')->whereIn('locale', ['fr', 'wo']);

Route::middleware('guest')->group(function () {
    Route::get('/connexion', [ConnexionController::class, 'create'])->name('login');
    Route::post('/connexion', [ConnexionController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/inscription', [InscriptionController::class, 'create'])->name('register');
    Route::post('/inscription', [InscriptionController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/activation', [ActivationController::class, 'create'])->name('activation');
    Route::post('/activation', [ActivationController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/mot-de-passe', [MotDePasseController::class, 'demande'])->name('password.request');
    Route::post('/mot-de-passe', [MotDePasseController::class, 'envoyer'])->name('password.email');
    Route::get('/mot-de-passe/{token}', [MotDePasseController::class, 'reinitialiser'])->name('password.reset');
    Route::post('/mot-de-passe/reinitialiser', [MotDePasseController::class, 'enregistrer'])->name('password.update');
});

Route::post('/deconnexion', [ConnexionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'actif'])->group(function () {
    Route::get('/tableau-de-bord', [RedirectionController::class, 'tableau'])->name('dashboard');
    Route::get('/plaintes/{plainte}', [PlainteController::class, 'show'])->name('plaintes.show');
    Route::post('/plaintes/{plainte}/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/plaintes/{plainte}/pieces/{piece}', [PieceJointeController::class, 'download'])->name('pieces.download');

    Route::get('/profil', [ProfilController::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');
    Route::get('/notifications', [AlerteController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/lire', [AlerteController::class, 'toutLire'])->name('notifications.read-all');
    Route::post('/notifications/{alerte}/lire', [AlerteController::class, 'lire'])->name('notifications.read');

    Route::middleware('role:citoyen')->prefix('citoyen')->name('citoyen.')->group(function () {
        Route::get('/', [TableauBordController::class, 'index'])->name('dashboard');
        Route::get('/plaintes', [CitoyenController::class, 'index'])->name('plaintes.index');
        Route::get('/plaintes/nouvelle', [CitoyenController::class, 'create'])->name('plaintes.create');
        Route::post('/plaintes', [CitoyenController::class, 'store'])->name('plaintes.store');
        Route::post('/plaintes/{plainte}/complement', [CitoyenController::class, 'completer'])->name('plaintes.completer');
        Route::post('/plaintes/{plainte}/evaluation', [CitoyenController::class, 'evaluer'])->name('plaintes.evaluer');
    });

    Route::middleware('role:autorite')->prefix('autorite')->name('autorite.')->group(function () {
        Route::get('/', [AutoriteController::class, 'dashboard'])->name('dashboard');
        Route::get('/dossiers', [AutoriteController::class, 'index'])->name('dossiers.index');
        Route::post('/dossiers/{plainte}/prendre', [AutoriteController::class, 'prendre'])->name('dossiers.prendre');
        Route::post('/dossiers/{plainte}/complement', [AutoriteController::class, 'complement'])->name('dossiers.complement');
        Route::post('/dossiers/{plainte}/resoudre', [AutoriteController::class, 'resoudre'])->name('dossiers.resoudre');
        Route::post('/dossiers/{plainte}/rejeter', [AutoriteController::class, 'rejeter'])->name('dossiers.rejeter');
    });

    Route::middleware('role:superviseur')->prefix('superviseur')->name('superviseur.')->group(function () {
        Route::get('/', [SuperviseurController::class, 'dashboard'])->name('dashboard');
        Route::get('/plaintes', [SuperviseurController::class, 'index'])->name('plaintes.index');
        Route::get('/plaintes/export', [SuperviseurController::class, 'export'])->name('plaintes.export');
        Route::post('/plaintes/{plainte}/analyser', [SuperviseurController::class, 'analyser'])->name('plaintes.analyser');
        Route::post('/plaintes/{plainte}/assigner', [SuperviseurController::class, 'assigner'])->name('plaintes.assigner');
        Route::post('/plaintes/{plainte}/escalader', [SuperviseurController::class, 'escalader'])->name('plaintes.escalader');
        Route::post('/plaintes/{plainte}/clore', [SuperviseurController::class, 'clore'])->name('plaintes.clore');
        Route::get('/autorites', [SuperviseurController::class, 'autorites'])->name('autorites.index');
        Route::get('/autorites/nouvelle', [SuperviseurController::class, 'createAutorite'])->name('autorites.create');
        Route::post('/autorites', [SuperviseurController::class, 'storeAutorite'])->name('autorites.store');
    });
});
