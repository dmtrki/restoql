<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Dyrynda\Database\Casts\EfficientUuid;


class ProductAttribute extends Pivot
{
    protected $table = 'product_attribute';

    public $incrementing = false;

    protected $fillable = [
        'product_id',
        'product_uuid',
        'attribute_id',
        'attribute_uuid',
        'value'
    ];

    protected $casts = [
        'product_uuid' => EfficientUuid::class,
        'attribute_uuid' => EfficientUuid::class,
    ];


    public function product() {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function attribute() {
        return $this->belongsTo(Attribute::class, 'attribute_uuid', 'uuid');
    }
}
