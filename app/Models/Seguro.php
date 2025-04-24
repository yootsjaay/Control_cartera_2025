<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Seguro
 * 
 * @property int $id
 * @property string $nombre
 * @property int $ramo_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Ramo $ramo
 * @property Collection|Compania[] $companias
 * @property Collection|Poliza[] $polizas
 *
 * @package App\Models
 */
class Seguro extends Model
{
	protected $table = 'seguros';

	protected $casts = [
		'ramo_id' => 'int'
	];

	protected $fillable = [
		'nombre',
		'ramo_id'
	];

	public function ramo()
	{
		return $this->belongsTo(Ramo::class);
	}

	public function companias()
	{
		return $this->belongsToMany(Compania::class)
					->withPivot('id')
					->withTimestamps();
	}

	public function polizas()
	{
		return $this->hasMany(Poliza::class);
	}
}
