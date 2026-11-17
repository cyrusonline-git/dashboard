<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Voorraadcheck (herbouw van de oude Voorraad tool): aanvragen bij collega-depots met reactie via
 * knoppen in de mail, en minimale voorraad per depot/subgroep voor de werkplaatslijst.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voorraad_aanvragen', function (Blueprint $t) {
            $t->id();
            $t->string('nummer', 20)->index();              // contract- of projectnummer waarvoor aangevraagd
            $t->string('soort', 10)->default('contract');    // contract | project
            $t->json('contracten')->nullable();              // onderliggende contracten (project)
            $t->string('klant')->nullable();
            $t->string('eigen_depot', 10);                   // aanvragend depot (nummer)
            $t->string('eigen_depot_naam')->nullable();
            $t->string('depot', 10)->index();                // aangeschreven depot (nummer)
            $t->string('depot_naam')->nullable();
            $t->string('aan_email')->nullable();
            $t->string('cc')->nullable();
            $t->json('regels');                              // [{subgroep, omschrijving, aantal, toegezegd}]
            $t->unsignedInteger('aantal')->default(0);
            $t->date('verhuurdatum')->nullable();
            $t->date('reactie_voor')->nullable();
            $t->text('opmerking')->nullable();
            // verzonden → toegezegd | deels | afgewezen → ontvangen ; of mislukt / geannuleerd
            $t->string('status', 20)->default('verzonden')->index();
            $t->string('token', 40)->unique();
            $t->text('reactie')->nullable();
            $t->string('gereageerd_door')->nullable();
            $t->timestamp('gereageerd_op')->nullable();
            $t->text('fout')->nullable();
            $t->unsignedBigInteger('core_user_id')->nullable();
            $t->string('aanvrager_naam')->nullable();
            $t->string('aanvrager_email')->nullable();
            $t->timestamps();
        });

        Schema::create('min_voorraad', function (Blueprint $t) {
            $t->id();
            $t->string('depot', 10);
            $t->string('subgroep', 20);
            $t->string('omschrijving')->nullable();
            $t->unsignedInteger('minimum')->default(0);
            $t->string('gewijzigd_door')->nullable();
            $t->timestamps();
            $t->unique(['depot', 'subgroep']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('min_voorraad');
        Schema::dropIfExists('voorraad_aanvragen');
    }
};
