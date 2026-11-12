<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Basis: instellingen en logboek. De inhoudelijke tabellen volgen zodra de inhoud is bepaald. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $t) {
            $t->string('key')->primary();
            $t->text('value')->nullable();
            $t->timestamps();
        });

        Schema::create('audit_log', function (Blueprint $t) {
            $t->id();
            $t->string('wie')->nullable();
            $t->string('actie', 80)->index();
            $t->string('onderwerp')->nullable();
            $t->json('details')->nullable();
            $t->string('ip', 45)->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log');
        Schema::dropIfExists('settings');
    }
};
