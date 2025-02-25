<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Class Compania
 * 
 * @property int $id
 * @property string $nombre
 * @property string $slug
 * @property string $clase
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Poliza[] $polizas
 * @property Collection|Seguro[] $seguros
 *
 * @package App\Models
 */
class Compania extends Model
{
	protected $table = 'companias';

	protected $fillable = [
		'nombre',
		'slug',
		'clase'
	];

	public function polizas()
	{
		return $this->hasMany(Poliza::class);
	}

	public function seguros()
	{
		return $this->hasMany(Seguro::class);
	}
	protected static function boot()
    {
        parent::boot();
        static::saving(function ($compania) {
            $baseSlug = Str::slug($compania->nombre, '-');
            $slug = $baseSlug;
            $count = 1;
            while (Compania::where('slug', $slug)->where('id', '!=', $compania->id ?? 0)->exists()) {
                $slug = $baseSlug . '-' . $count++;
            }
            $compania->slug = $slug;
        });
    }
}
