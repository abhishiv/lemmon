<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class RestaurantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param Builder $builder
     * @param Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        //Isset because error on CUSTOMER menu view
        if (isset(auth()->user()->restaurant_id)) {
            $builder->where('restaurant_id', auth()->user()->restaurant_id);
        }
    }
}
