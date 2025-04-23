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
 * @property string $nombre
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|NumerosPoliza[] $numeros_polizas
 * @property Collection|Poliza[] $polizas
 * @property Collection|Seguro[] $seguros
 *
 * @package App\Models
 */
class Ramo extends Model
{
	protected $table = 'ramos';

	protected $fillable = [
		'nombre'
	];

	public function numeros_polizas()
	{
		return $this->hasMany(NumerosPoliza::class);
	}

	public function polizas()
	{
		return $this->hasMany(Poliza::class);
	}

	public function seguros()
	{
		return $this->hasMany(Seguro::class);
	}
}
