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
 * @property string $slug
 * @property int $id_seguros
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Seguro $seguro
 * @property Collection|Poliza[] $polizas
 *
 * @package App\Models
 */
class Ramo extends Model
{
	protected $table = 'ramos';

	protected $casts = [
		'id_seguros' => 'int'
	];

	protected $fillable = [
		'nombre_ramo',
		'slug',
		'id_seguros'
	];

	public function seguro()
	{
		return $this->belongsTo(Seguro::class, 'id_seguros');
	}

	public function polizas()
	{
		return $this->hasMany(Poliza::class);
	}
}
