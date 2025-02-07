<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Ramo
 * 
 * @property int $id
 * @property string $nombre_ramo
 * @property int $id_seguros
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Seguro $seguro
 *
 * @package App\Models
 */
class Ramo extends Model
{
	protected $table = 'ramos';

	protected $casts = [
		'id_seguros' => 'int'
	];

	protected $fillable = [
		'nombre_ramo',
		'id_seguros',
		'slug'
	];

	public function seguro()
	{
		return $this->belongsTo(Seguro::class, 'id_seguros');
	}
	protected static function boot()
    {
        parent::boot();
        static::saving(function ($ramo) {
            $ramo->slug = Str::slug($ramo->nombre_ramo, '-'); // Genera el slug basado en nombre_ramo
        });
    }
}
