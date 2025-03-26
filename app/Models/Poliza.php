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
 * @property int $user_id
 * 
 * @property Cliente $cliente
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
		'cliente_id',
		'user_id'
	];

	public function cliente()
	{
		return $this->belongsTo(Cliente::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
