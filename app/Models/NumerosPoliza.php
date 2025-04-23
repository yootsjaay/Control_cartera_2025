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
 * @property string $numero_polizas
 * @property int $compania_id
 * @property int $ramo_id
 * @property int $seguro_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Compania $compania
 * @property Ramo $ramo
 * @property Seguro $seguro
 * @property Collection|PagosFraccionado[] $pagos_fraccionados
 * @property Collection|Poliza[] $polizas
 *
 * @package App\Models
 */
class NumerosPoliza extends Model
{
	protected $table = 'numeros_polizas';

	protected $casts = [
		'compania_id' => 'int',
		'ramo_id' => 'int',
		'seguro_id' => 'int'
	];

	protected $fillable = [
		'numero_polizas',
		'compania_id',
		'ramo_id',
		'seguro_id'
	];

	public function compania()
	{
		return $this->belongsTo(Compania::class);
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
		return $this->hasMany(PagosFraccionado::class, 'numero_poliza_id');
	}

	public function polizas()
	{
		return $this->hasMany(Poliza::class, 'numero_poliza_id');
	}
}
