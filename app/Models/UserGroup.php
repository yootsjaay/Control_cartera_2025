<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class UserGroup
 * 
 * @property int $id
 * @property int $user_id
 * @property int $group_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Group $group
 * @property User $user
 *
 * @package App\Models
 */
class UserGroup extends Model
{
	protected $table = 'user_group';

	protected $casts = [
		'user_id' => 'int',
		'group_id' => 'int'
	];

	protected $fillable = [
		'user_id',
		'group_id'
	];

	public function group()
	{
		return $this->belongsTo(Group::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}
