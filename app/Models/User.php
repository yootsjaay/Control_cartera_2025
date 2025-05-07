<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;
	protected $table = 'users';

	protected $casts = [
		'group_id' => 'int',
		'email_verified_at' => 'datetime'
	];

	protected $hidden = [
		'password',
		'remember_token'
	];

	protected $fillable = [
		'group_id',
		'name',
		'email',
		'email_verified_at',
		'password',
		'remember_token'
	];

	public function group()
	{
		return $this->belongsTo(Group::class);
	}

	public function polizas()
	{
		return $this->hasMany(Poliza::class);
	}
}
