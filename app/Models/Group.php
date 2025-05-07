<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Group
 * 
 * @property int $id
 * @property string $nombre
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Collection|Poliza[] $polizas
 * @property Collection|User[] $users
 *
 * @package App\Models
 */
class Group extends Model
{
	protected $table = 'groups';

	protected $fillable = [
		'nombre'
	];

	public function polizas()
	{
		return $this->hasMany(Poliza::class);
	}

	public function users()
	{
		return $this->hasMany(User::class);
	}
}
