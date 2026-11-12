<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FactuurrunController;
use App\Http\Controllers\HuurController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

// Alles achter de CORE-login (middleware 'core' = cookie-relay naar Boels CORE)
Route::middleware('core')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/kies-rol', [RolController::class, 'kies'])->name('kies-rol');
    Route::post('/kies-rol', [RolController::class, 'opslaan'])->name('kies-rol.opslaan');
    Route::get('/geen-toegang', [RolController::class, 'geenToegang'])->name('geen-toegang');
    Route::get('/uitloggen', [RolController::class, 'uitloggen'])->name('uitloggen');

    // Planning (8.7 + 2.6): te leveren orders, offertes, op te schonen
    Route::get('/planning', [PlanningController::class, 'index'])->name('planning');
    Route::get('/planning/{nr}', [PlanningController::class, 'show'])->whereNumber('nr')->name('planning.show');
    Route::post('/planning/{nr}/notitie', [PlanningController::class, 'notitie'])->whereNumber('nr')->name('planning.notitie');

    // Uit factuurrun (met redenveld)
    Route::get('/factuurrun', [FactuurrunController::class, 'index'])->name('factuurrun');
    Route::get('/factuurrun/{nr}', [FactuurrunController::class, 'show'])->whereNumber('nr')->name('factuurrun.show');
    Route::post('/factuurrun/{nr}/reden', [FactuurrunController::class, 'notitie'])->whereNumber('nr')->name('factuurrun.reden');

    // In huur (WOW): contracten, ophaallijst, AVI, machine
    Route::get('/in-huur', [HuurController::class, 'index'])->name('huur');
    Route::get('/in-huur/ophalen', [HuurController::class, 'ophalen'])->name('huur.ophalen');
    Route::get('/in-huur/avi', [HuurController::class, 'avi'])->name('huur.avi');
    Route::get('/in-huur/machine/{nummer}', [HuurController::class, 'machine'])->name('huur.machine');
    Route::get('/in-huur/{nr}', [HuurController::class, 'show'])->whereNumber('nr')->name('huur.show');

    // Planbord: aankomende orders op tijd (alleen-lezen), transfers apart
    Route::get('/planbord', [\App\Http\Controllers\PlanbordController::class, 'index'])->name('planbord');

    // Beheer (admin)
    Route::middleware('rol:admin')->group(function () {
        Route::get('/beheer', [\App\Http\Controllers\BeheerController::class, 'index'])->name('beheer');
        Route::post('/beheer', [\App\Http\Controllers\BeheerController::class, 'opslaan'])->name('beheer.opslaan');
    });

    // Team: acties per persoon (managers/beheer)
    Route::get('/team', [TeamController::class, 'index'])->name('team');
});
