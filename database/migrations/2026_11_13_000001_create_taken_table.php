<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Vrije kaartjes op het planbord (zoals de groene VOORRAAD-kaarten bij H&H): eigen data van deze app. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taken', function (Blueprint $t) {
            $t->id();
            $t->string('titel');
            $t->text('omschrijving')->nullable();
            $t->unsignedBigInteger('employee_id')->nullable()->index(); // kolom (CORE employee_id), NULL = te plannen
            $t->date('deadline')->nullable();
            $t->unsignedTinyInteger('prioriteit')->default(0);         // 0 normaal, 1 hoog
            $t->timestamp('klaar_op')->nullable();
            $t->string('gemaakt_door')->nullable();
            $t->string('klaar_door')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taken');
    }
};
