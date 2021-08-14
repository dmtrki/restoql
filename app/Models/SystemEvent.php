<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;

class SystemEvent extends Model
{
    use GeneratesUuid;

    protected $fillable = [
        'id',
        'uuid',
        'user_id',
        'type_code',
        'status_code',
        'details'
    ];

    protected $casts = [
        'uuid' => EfficientUuid::class,
        'details' => 'array'
    ];
}
