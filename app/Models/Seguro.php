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
	protected $table = 'seguros';

	

	public function polizas()
	{
		return $this->hasMany(Poliza::class);
	}
	// Relación con Ramo (un Seguro pertenece a un Ramo)
    public function ramo()
    {
        return $this->belongsTo(Ramo::class, 'ramo_id'); // Especifica la clave foránea
    }

    // Relación con Compañías (muchos a muchos)
    public function companias()
    {
        return $this->belongsToMany(Compania::class, 'compania_seguro');
    }
}
