<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Poliza
 * 
 * @property int $id
 * @property int $ramo_id
 * @property int $seguro_id
 * @property int $numero_poliza_id
 * @property string $nombre_cliente
 * @property Carbon $vigencia_inicio
 * @property Carbon $vigencia_fin
 * @property string $forma_pago
 * @property float $prima_total
 * @property string $ruta_pdf
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property NumerosPoliza $numeros_poliza
 * @property Ramo $ramo
 * @property Seguro $seguro
 *
 * @package App\Models
 */
class Poliza extends Model
{
	protected $table = 'polizas';

	protected $casts = [
		'ramo_id' => 'int',
		'seguro_id' => 'int',
		'numero_poliza_id' => 'int',
		'vigencia_inicio' => 'datetime',
		'vigencia_fin' => 'datetime',
		'prima_total' => 'float'
	];

	protected $fillable = [
		'ramo_id',
		'seguro_id',
		'numero_poliza_id',
		'nombre_cliente',
		'vigencia_inicio',
		'vigencia_fin',
		'forma_pago',
		'prima_total',
		'ruta_pdf'
	];

	public function numeros_poliza()
	{
		return $this->belongsTo(NumerosPoliza::class, 'numero_poliza_id');
	}

	public function ramo()
	{
		return $this->belongsTo(Ramo::class);
	}

	public function seguro()
	{
		return $this->belongsTo(Seguro::class);
	}
}
