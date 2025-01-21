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
 * @property int $compania_id
 * @property string $nombre
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Compania $compania
 * @property Collection|Poliza[] $polizas
 * @property Collection|Ramo[] $ramos
 *
 * @package App\Models
 */
class Seguro extends Model
{
	protected $table = 'seguros';

	protected $casts = [
		'compania_id' => 'int'
	];

	protected $fillable = [
		'compania_id',
		'nombre'
	];

	public function compania()
	{
		return $this->belongsTo(Compania::class);
	}

	public function polizas()
	{
		return $this->hasMany(Poliza::class);
	}

	public function ramos()
	{
		return $this->hasMany(Ramo::class, 'id_seguros');
	}
}
