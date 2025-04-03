<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CompaniaSeguro
 * 
 * @property int $id
 * @property int $seguro_id
 * @property int $compania_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Compania $compania
 * @property Seguro $seguro
 *
 * @package App\Models
 */
class CompaniaSeguro extends Model
{
	protected $table = 'compania_seguro';

	protected $casts = [
		'seguro_id' => 'int',
		'compania_id' => 'int'
	];

	protected $fillable = [
		'seguro_id',
		'compania_id'
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
