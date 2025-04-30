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
 * @property int $poliza_id
 * @property int $numero_recibo
 * @property Carbon $vigencia_inicio
 * @property Carbon $vigencia_fin
 * @property float $importe
 * @property Carbon $fecha_limite_pago
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Poliza $poliza
 *
 * @package App\Models
 */
class PagosFraccionado extends Model
{
	protected $table = 'pagos_fraccionados';

	protected $casts = [
		'poliza_id' => 'int',
		'numero_recibo' => 'int',
		'vigencia_inicio' => 'datetime',
		'vigencia_fin' => 'datetime',
		'importe' => 'float',
		'fecha_limite_pago' => 'datetime'
	];

	protected $fillable = [
		'poliza_id',
		'numero_recibo',
		'vigencia_inicio',
		'vigencia_fin',
		'importe',
		'fecha_limite_pago'
	];

	public function poliza()
	{
		return $this->belongsTo(Poliza::class);
	}
}
