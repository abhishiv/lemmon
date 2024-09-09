<?php

namespace App\Payment;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    use HasFactory;

    protected $table = 'order_items';

    protected $guarded = [];

    protected $with = [
        'products'
    ];

    public function products(): belongsTo
    {
        return $this->belongsTo(Product::class, 'product_id')->withTrashed();
    }
}
