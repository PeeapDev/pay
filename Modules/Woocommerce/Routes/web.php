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

Route::group(config('addons.route_group.authenticated.admin'), function() {
    Route::get('addon/woocommerce/configure', 'WoocommerceController@configure')->name('addon.woocommerce.configure');
    Route::post('addon/woocommerce/store', 'WoocommerceController@store')->name('addon.woocommerce.store');
});

