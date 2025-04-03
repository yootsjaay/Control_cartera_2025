<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable
{
	use HasFactory, Notifiable, HasRoles;

	protected $casts = [
		'email_verified_at' => 'datetime'
	];

	protected $hidden = [
		'password',
		'remember_token'
	];

	protected $fillable = [
		'name',
		'email',
		'avatar',
		'telefono',
		'email_verified_at',
		'password',
		'remember_token'
	];

	public function polizas()
	{
		return $this->hasMany(Poliza::class);
	}
}
