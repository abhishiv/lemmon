<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PartialPayment extends Model
{
    protected $table = 'payments_partial';

    protected $fillable = [
        'restaurant_id',
        'table_id',
        'user_id',
        'orders',
        'tips',
        'discount',
        'discount_type',
        'cart',
        'method',
        'amount',
        'selection',
    ];

    /**
     * The orders that belong to the payment.
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_partial_payment', 'payment_id', 'order_id');
    }
}
