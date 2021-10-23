<?php

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('products', 'ProductCrudController');
    Route::crud('product-categories', 'ProductCategoryCrudController');
    Route::crud('product-reviews', 'ProductReviewCrudController');
    Route::crud('attribute-groups', 'AttributeGroupCrudController');
    Route::crud('attributes', 'AttributeCrudController');
    Route::crud('manufacturers', 'ManufacturerCrudController');
    Route::crud('projects', 'ProjectCrudController');
    Route::crud('project-categories', 'ProjectCategoryCrudController');
    Route::crud('stories', 'StoryCrudController');
    Route::crud('leads', 'LeadCrudController');
    Route::crud('orders', 'OrderCrudController');
    Route::crud('customers', 'CustomerCrudController');
    Route::crud('currencies', 'CurrencyCrudController');
    Route::crud('tag', 'TagCrudController');
    Route::crud('menu', 'MenuCrudController');
    Route::crud('menu-item', 'MenuItemCrudController');
    Route::crud('page', 'PageCrudController');
}); // this should be the absolute last line of this file