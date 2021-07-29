<?php

if (config('laraberg.use_package_routes')) {
    Route::group(['prefix' => config('laraberg.prefix'), 'middleware' => config('laraberg.middlewares')], function () {
        Route::apiResource('blocks', 'VanOns\Gutereca\Http\Controllers\BlockController');
        Route::get('oembed', 'VanOns\Gutereca\Http\Controllers\OEmbedController');
    });
};
