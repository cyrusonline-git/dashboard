<?php

use Illuminate\Support\Facades\Artisan;

// Nog geen geplande taken. Zodra die er komen: in DirectAdmin
// * * * * * php /home/deb2003831/domains/dashboard.sorai.nl/laravel_app/artisan schedule:run >/dev/null 2>&1
Artisan::command('dashboard:info', function () {
    $this->info('Boels Dashboard — CORE: '.config('core.url').' · slug: '.config('core.slug'));
})->purpose('Configuratie tonen');
