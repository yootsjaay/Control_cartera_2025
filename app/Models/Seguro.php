<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Seguro
 * 
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Compania[] $companias
 * @property Collection|Poliza[] $polizas
 *
 * @package App\Models
 */
class Seguro extends Model
{
    public function ramos()
    {
        return $this->hasMany(Ramo::class);
    }

}
