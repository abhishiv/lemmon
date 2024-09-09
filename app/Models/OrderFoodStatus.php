<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderFoodStatus extends Model
{
    use HasFactory;

    protected $guarded = [];

    const NEW = 'new';
    const PREPARING = 'preparing';
    const READY = 'ready';
    const CLOSED = 'closed';

    public function order() {
        return $this->belongsTo(Order::class);
    }

    public function foodType() {
        return $this->belongsTo(FoodType::class);
    }
}
