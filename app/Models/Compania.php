<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;


class Compañía extends Model
{
    public function ramos()
    {
        return $this->belongsToMany(Ramo::class, 'compañía_ramo');
    }
}