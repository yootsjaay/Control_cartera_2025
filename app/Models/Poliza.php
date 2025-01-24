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
 * @property string $numero_poliza
 * @property Carbon $vigencia_inicio
 * @property Carbon $vigencia_fin
 * @property string $forma_pago
 * @property float $total_a_pagar
 * @property string|null $archivo_pdf
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int $cliente_id
 * @property int $compania_id
 * @property int $seguro_id
 * @property int $ramo_id
 * @property int|null $creado_por
 * 
 * @property Cliente $cliente
 * @property Compania $compania
 * @property User|null $user
 * @property Ramo $ramo
 * @property Seguro $seguro
 *
 * @package App\Models
 */
class Poliza extends Model
{
	protected $table = 'polizas';

	protected $casts = [
		'vigencia_inicio' => 'datetime',
		'vigencia_fin' => 'datetime',
		'total_a_pagar' => 'float',
		'cliente_id' => 'int',
		'compania_id' => 'int',
		'seguro_id' => 'int',
		'ramo_id' => 'int',
		'creado_por' => 'int'
	];

	protected $fillable = [
		'numero_poliza',
		'vigencia_inicio',
		'vigencia_fin',
		'forma_pago',
		'total_a_pagar',
		'archivo_pdf',
		'status',
		'cliente_id',
		'compania_id',
		'seguro_id',
		'ramo_id',
		'creado_por'
	];

	public function cliente()
	{
		return $this->belongsTo(Cliente::class);
	}

	public function compania()
	{
		return $this->belongsTo(Compania::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'creado_por');
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
