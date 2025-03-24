<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CompaniaSeguro
 * 
 * @property int $compania_id
 * @property int $seguro_id
 * 
 * @property Compania $compania
 * @property Seguro $seguro
 *
 * @package App\Models
 */
class CompaniaSeguro extends Model
{
	protected $table = 'compania_seguro';
	public $incrementing = false;
	public $timestamps = false;

	protected $casts = [
		'compania_id' => 'int',
		'seguro_id' => 'int'
	];

	public function compania()
	{
		return $this->belongsTo(Compania::class);
	}

	public function seguro()
	{
		return $this->belongsTo(Seguro::class);
	}
}
