<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FactuurrunController;
use App\Http\Controllers\HuurController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

// Reactie van een collega-depot op een materieelaanvraag: ondertekende link uit de mail, zonder CORE-login
Route::middleware('signed')->group(function () {
    Route::get('/aanvraag/{token}', [\App\Http\Controllers\AanvraagReactieController::class, 'toon'])->name('aanvraag.reactie');
    Route::post('/aanvraag/{token}', [\App\Http\Controllers\AanvraagReactieController::class, 'opslaan'])->name('aanvraag.reactie.opslaan');
});

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

    // PO-bewaking: zonder klantreferentie/PO-nummer, gevolgd tot de referentie in Smartview staat
    Route::get('/referenties', [\App\Http\Controllers\ReferentieController::class, 'index'])->name('referenties');
    Route::post('/referenties/{nr}/notitie', [\App\Http\Controllers\ReferentieController::class, 'notitie'])->whereNumber('nr')->name('referenties.notitie');

    // In huur (WOW): contracten, ophaallijst, AVI, machine
    Route::get('/in-huur', [HuurController::class, 'index'])->name('huur');
    Route::get('/in-huur/ophalen', [HuurController::class, 'ophalen'])->name('huur.ophalen');
    Route::get('/in-huur/avi', [HuurController::class, 'avi'])->name('huur.avi');
    Route::get('/in-huur/machine/{nummer}', [HuurController::class, 'machine'])->name('huur.machine');
    Route::get('/in-huur/{nr}', [HuurController::class, 'show'])->whereNumber('nr')->name('huur.show');

    // Voorraadcheck (herbouw Voorraad tool): contract/project → eigen voorraad, collega-depots, aanvragen
    Route::get('/voorraad', [\App\Http\Controllers\VoorraadController::class, 'index'])->name('voorraad');
    Route::get('/voorraad/aanvragen', [\App\Http\Controllers\AanvraagController::class, 'index'])->name('voorraad.aanvragen');
    Route::get('/voorraad/aanvragen/{aanvraag}', [\App\Http\Controllers\AanvraagController::class, 'show'])->whereNumber('aanvraag')->name('voorraad.aanvraag');
    Route::post('/voorraad/aanvragen/{aanvraag}/status', [\App\Http\Controllers\AanvraagController::class, 'status'])->whereNumber('aanvraag')->name('voorraad.aanvraag.status');
    Route::get('/voorraad/werkplaats', [\App\Http\Controllers\MinimumController::class, 'werkplaats'])->name('voorraad.werkplaats');
    Route::get('/voorraad/minimum', [\App\Http\Controllers\MinimumController::class, 'index'])->name('voorraad.minimum');
    Route::post('/voorraad/minimum', [\App\Http\Controllers\MinimumController::class, 'opslaan'])->name('voorraad.minimum.opslaan');
    Route::post('/voorraad/minimum/kopieer', [\App\Http\Controllers\MinimumController::class, 'kopieer'])->name('voorraad.minimum.kopieer');
    Route::get('/voorraad/planner', [\App\Http\Controllers\StockplannerController::class, 'index'])->name('voorraad.planner');
    Route::get('/voorraad/{nr}', [\App\Http\Controllers\VoorraadController::class, 'show'])->whereNumber('nr')->name('voorraad.show');
    Route::get('/voorraad/{nr}/aanvraag/{depot}', [\App\Http\Controllers\VoorraadController::class, 'nieuw'])->whereNumber('nr')->name('voorraad.nieuw');
    Route::post('/voorraad/{nr}/aanvraag/{depot}', [\App\Http\Controllers\VoorraadController::class, 'verstuur'])->whereNumber('nr')->name('voorraad.verstuur');
    Route::get('/vloot', [\App\Http\Controllers\VlootController::class, 'index'])->name('vloot');

    // Planbord: aankomende orders op tijd (alleen-lezen), transfers apart
    Route::get('/planbord', [\App\Http\Controllers\PlanbordController::class, 'index'])->name('planbord');

    // Beheer (admin)
    Route::middleware('rol:admin')->group(function () {
        Route::get('/beheer', [\App\Http\Controllers\BeheerController::class, 'index'])->name('beheer');
        Route::post('/beheer', [\App\Http\Controllers\BeheerController::class, 'opslaan'])->name('beheer.opslaan');
    });

    // Managementoverzicht (managers/beheer)
    Route::get('/overzicht', [\App\Http\Controllers\OverzichtController::class, 'index'])->name('overzicht');

    // Team: acties per persoon (managers/beheer)
    Route::get('/team', [TeamController::class, 'index'])->name('team');
});
