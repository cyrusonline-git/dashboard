<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Minimale voorraad per depot en subgroep (werkplaatslijst). */
class MinVoorraad extends Model
{
    protected $table = 'min_voorraad';

    protected $guarded = [];

    protected $casts = ['minimum' => 'integer'];
}
