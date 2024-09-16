<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::group(['namespace' => 'Api', 'middleware' => ['auth:api-v2']], function()
{
    Route::get('exchange-direction', 'CryptoExchangeApiController@exchangeDirection');
    Route::post('direction-to-currencies', 'CryptoExchangeApiController@directionToCurrencies');
    Route::post('direction-amount', 'CryptoExchangeApiController@exchangeAmount');
    Route::post('confirm-exchange', 'CryptoExchangeApiController@confirmExchange');
    Route::post('process-exchange', 'CryptoExchangeApiController@cryptoExchangeProcess');

});
