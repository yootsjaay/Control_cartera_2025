<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Poliza
 * 
 * @property int $id
 * @property int $ramo_id
 * @property int $seguro_id
 * @property int $numero_poliza_id
 * @property int $compania_id
 * @property string $nombre_cliente
 * @property Carbon $vigencia_inicio
 * @property Carbon $vigencia_fin
 * @property string|null $forma_pago
 * @property float $prima_total
 * @property Carbon|null $primer_pago_fraccionado
 * @property string $tipo_prima
 * @property string $ruta_pdf
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Compania $compania
 * @property NumerosPoliza $numeros_poliza
 * @property Ramo $ramo
 * @property Seguro $seguro
 * @property Collection|PagosFraccionado[] $pagos_fraccionados
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
		'compania_id' => 'int',
		'vigencia_inicio' => 'datetime',
		'vigencia_fin' => 'datetime',
		'prima_total' => 'float',
		'primer_pago_fraccionado' => 'datetime'
	];

	protected $fillable = [
		'ramo_id',
		'seguro_id',
		'numero_poliza_id',
		'compania_id',
		'nombre_cliente',
		'vigencia_inicio',
		'vigencia_fin',
		'forma_pago',
		'prima_total',
		'primer_pago_fraccionado',
		'tipo_prima',
		'ruta_pdf'
	];

	public function compania()
	{
		return $this->belongsTo(Compania::class);
	}

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

	public function pagos_fraccionados()
	{
		return $this->hasMany(PagosFraccionado::class);
	}

	
}
