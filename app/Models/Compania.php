<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Compania
 * 
 * @property int $id
 * @property string $nombre
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Seguro[] $seguros
 *
 * @package App\Models
 */
class Compania extends Model
{
	protected $table = 'companias';

	protected $fillable = [
		'nombre'
	];

	public function seguros()
	{
		return $this->belongsToMany(Seguro::class)
					->withPivot('id')
					->withTimestamps();
	}

}
