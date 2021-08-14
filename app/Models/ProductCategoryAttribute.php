<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Dyrynda\Database\Casts\EfficientUuid;

class ProductCategoryAttribute extends Pivot
{
    use HasFactory;

    protected $fillable = [
        'product_category_uuid',
        'attribute_uuid',
        'values'
    ];

    protected $casts = [
    'product_category_uuid' => EfficientUuid::class,
    'attribute_uuid' => EfficientUuid::class,
    'values' => 'array',
    ];


    public function category() {
        return $this->belongsTo(ProductCategory::class, 'product_category_uuid', 'uuid');
    }

    public function attribute() {
        return $this->belongsTo(Attribute::class, 'attribute_uuid', 'uuid');
    }

}
