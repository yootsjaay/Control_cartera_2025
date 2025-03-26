<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Ramo
 * 
 * @property int $id
 * @property string $nombre_ramo
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Poliza[] $polizas
 *
 * @package App\Models
 */
class Ramo extends Model
{
    public function seguro()
    {
        return $this->belongsTo(Seguro::class);
    }

    public function compañías()
    {
        return $this->belongsToMany(Compañía::class, 'compañía_ramo');
    }

}
