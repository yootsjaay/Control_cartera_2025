<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToGroup
{
    public static function bootBelongsToGroup()
    {
        static::addGlobalScope('group', function (Builder $builder) {
            $user = auth()->user();
            if ($user && !$user->hasRole('admin')) {
                $builder->where('group_id', $user->group_id);
            }
        });

        // Autoasignación del grupo al crear
        static::creating(function ($model) {
            $user = auth()->user();
            if ($user && !$user->hasRole('admin') && isset($model->group_id)) {
                $model->group_id = $user->group_id;
            }
        });
    }
}
