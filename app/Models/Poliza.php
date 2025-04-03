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
 * @property int $compania_id
 * @property int $cliente_id
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Cliente $cliente
 * @property Compania $compania
 * @property User $user
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
		'compania_id' => 'int',
		'cliente_id' => 'int',
		'user_id' => 'int'
	];

	protected $fillable = [
		'numero_poliza',
		'vigencia_inicio',
		'vigencia_fin',
		'forma_pago',
		'total_a_pagar',
		'archivo_pdf',
		'status',
		'compania_id',
		'cliente_id',
		'user_id'
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
		return $this->belongsTo(User::class);
	}
	// Relación a través de la tabla intermedia
	public function companiaSeguros()
	{
		return $this->hasManyThrough(
			CompaniaSeguro::class,  // Modelo de pivote
			Compania::class,        // Modelo de relación intermedia
			'compania_id',          // Clave foránea en `Poliza` hacia `Compania`
			'seguro_id',            // Clave foránea en `CompaniaSeguro` hacia `Seguro`
			'id',                   // Clave local en `Poliza`
			'id'                    // Clave local en `CompaniaSeguro`
		);
	}

// Relación a través de la tabla intermedia para acceder a Seguro
public function seguro()
{
    return $this->hasOneThrough(Seguro::class, CompaniaSeguro::class, 'compania_id', 'id', 'compania_id', 'seguro_id');
}
}
