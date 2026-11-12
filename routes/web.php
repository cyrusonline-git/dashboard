<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RolController;
use Illuminate\Support\Facades\Route;

// Alles achter de CORE-login (middleware 'core' = cookie-relay naar Boels CORE)
Route::middleware('core')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/kies-rol', [RolController::class, 'kies'])->name('kies-rol');
    Route::post('/kies-rol', [RolController::class, 'opslaan'])->name('kies-rol.opslaan');
    Route::get('/geen-toegang', [RolController::class, 'geenToegang'])->name('geen-toegang');
    Route::get('/uitloggen', [RolController::class, 'uitloggen'])->name('uitloggen');
});
