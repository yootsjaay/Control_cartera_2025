<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PagosFraccionado
 * 
 * @property int $id
 * @property int $numero_poliza_id
 * @property Carbon $vigencia_inicio
 * @property Carbon $vigencia_fin
 * @property float $importe
 * @property Carbon $fecha_limite
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property NumerosPoliza $numeros_poliza
 *
 * @package App\Models
 */
class PagosFraccionado extends Model
{
	protected $table = 'pagos_fraccionados';

	protected $casts = [
		'numero_poliza_id' => 'int',
		'vigencia_inicio' => 'datetime',
		'vigencia_fin' => 'datetime',
		'importe' => 'float',
		'fecha_limite' => 'datetime'
	];

	protected $fillable = [
		'numero_poliza_id',
		'vigencia_inicio',
		'vigencia_fin',
		'importe',
		'fecha_limite'
	];

	public function numeros_poliza()
	{
		return $this->belongsTo(NumerosPoliza::class, 'numero_poliza_id');
	}
}
