<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Dyrynda\Database\Casts\EfficientUuid;

class ProductPrice extends Model
{

    protected $fillable = [
        'id',
        'product_id',
        'product_uuid',
        'currency_id',
        'price_origin',
        'price'
    ];

    protected $casts = [
        'product_uuid' => EfficientUuid::class,
    ];

    public function product(): belongsTo
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function currency(): belongsTo
    {
        return $this->belongsTo(Currency::class);
    }
}
