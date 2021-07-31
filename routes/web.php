<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/test', ['App\Http\Controllers\ServiceController','test']);
Route::get('/import/cats', ['App\Http\Controllers\ServiceController','importCategories']);
Route::get('/import/prods', ['App\Http\Controllers\ServiceController','importProducts']);
Route::get('/import/curs', ['App\Http\Controllers\ServiceController','addCurrencies']);

// Route::mediaLibrary();
