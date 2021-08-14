<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Dyrynda\Database\Casts\EfficientUuid;

class ProductDiscount extends Model
{
    protected $fillable = [
        'id',
        'product_uuid',
        'currency_id',
        'starting_at',
        'finishing_at',
        'value',
    ];

    protected $casts = [
        'product_uuid' => EfficientUuid::class,
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_uuid', 'uuid');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
