<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Ramo
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
class Ramo extends Model
{
	protected $table = 'ramos';

	protected $fillable = [
		'nombre'
	];

	public function seguros()
	{
		return $this->hasMany(Seguro::class);
	}
	public function companias()
{
    return $this->belongsToMany(Compania::class, 'compania_ramo'); // Asegúrate de usar el nombre correcto de la tabla pivot
}


}
