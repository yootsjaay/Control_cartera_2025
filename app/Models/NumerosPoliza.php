<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class NumerosPoliza
 * 
 * @property int $id
 * @property string $numero_poliza
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|PagosFraccionado[] $pagos_fraccionados
 * @property Collection|Poliza[] $polizas
 *
 * @package App\Models
 */
class NumerosPoliza extends Model
{
	protected $table = 'numeros_polizas';

	protected $fillable = [
		'numero_poliza'
	];

	public function pagos_fraccionados()
	{
		return $this->hasMany(PagosFraccionado::class, 'numero_poliza_id');
	}

	public function polizas()
	{
		return $this->hasMany(Poliza::class, 'numero_poliza_id');
	}
}
