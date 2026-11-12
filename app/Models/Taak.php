<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Taak extends Model
{
    protected $table = 'taken';

    protected $fillable = ['titel', 'omschrijving', 'employee_id', 'deadline', 'prioriteit', 'klaar_op', 'gemaakt_door', 'klaar_door'];

    protected $casts = ['deadline' => 'date:Y-m-d', 'klaar_op' => 'datetime:Y-m-d H:i', 'prioriteit' => 'integer'];
}
