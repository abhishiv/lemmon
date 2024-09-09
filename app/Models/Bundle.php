<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bundle extends Model
{
    use HasFactory;

    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function extras()
    {
        return $this->belongsToMany(Extra::class, 'extra_bundle', 'bundle_id', 'entity_id')->withPivot(['order', 'price'])->where('entity_type', 'extra');
    }

    public function removables()
    {
        return $this->belongsToMany(Extra::class, 'extra_bundle', 'bundle_id', 'entity_id')->withPivot(['order'])->where('entity_type', 'removable');
    }

    public function extraProducts()
    {
        return $this->belongsToMany(Product::class, 'extra_bundle', 'bundle_id', 'entity_id')->withPivot(['order', 'price'])->where('entity_type', 'product');
    }

    public function combinedExtras()
    {
        $extras = $this->extras->toArray();
        $extraProducts = $this->extraProducts->toArray();

        return array_merge($extras, $extraProducts);
    }

}
