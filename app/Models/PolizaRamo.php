<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PolizaRamo
 * 
 * @property int $id
 * @property int $poliza_id
 * @property int $ramo_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Poliza $poliza
 * @property Ramo $ramo
 *
 * @package App\Models
 */
class PolizaRamo extends Model
{
	protected $table = 'poliza_ramo';

	protected $casts = [
		'poliza_id' => 'int',
		'ramo_id' => 'int'
	];

	protected $fillable = [
		'poliza_id',
		'ramo_id'
	];

	public function poliza()
	{
		return $this->belongsTo(Poliza::class);
	}

	public function ramo()
	{
		return $this->belongsTo(Ramo::class);
	}
}
