<?php

use VanOns\Gutereca\Models\Block;
use VanOns\Gutereca\Models\Content;

return [
    /*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    */

    'use_package_routes' => true,

    'middlewares' => ['web', 'auth'],

    'prefix' => 'gutereca',

    "models" => [
        "block" => Block::class,
        "content" => Content::class,
    ],
    
];
