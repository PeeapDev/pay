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

# Crypto Exchange Admin section

Route::group(config('addons.route_group.authenticated.admin'), function () {

    Route::group(['namespace' => 'Admin'], function () {
        Route::name('admin.crypto_direction.')->group(function () {
            Route::get('exchange/directions', 'ExchangeDirectionController@index')->middleware(['permission:view_crypto_direction'])->name('index');
            Route::get('exchange/getcurrency', 'ExchangeDirectionController@getCurrency')->name('currencies');
            Route::get('exchange/direction/create', 'ExchangeDirectionController@create')->middleware(['permission:add_crypto_direction'])->name('create');
            Route::post('exchange/direction/store', 'ExchangeDirectionController@store')->name('store');
            Route::get('exchange/direction/edit/{id}', 'ExchangeDirectionController@edit')->middleware(['permission:edit_crypto_direction'])->name('edit');
            Route::post('exchange/direction/update/{id}', 'ExchangeDirectionController@update')->name('update');
            Route::get('exchange/direction/delete/{id}', 'ExchangeDirectionController@delete')->middleware(['permission:delete_crypto_direction'])->name('delete');
            Route::get('/crypto-direction-gateway', 'ExchangeDirectionController@directionGateway')->name('gateway');
        });

        Route::name('admin.crypto_exchanges.')->group(function () {
            Route::get('crypto_exchanges', 'CryptoExchangeController@index')->middleware(['permission:view_crypto_exchange_transaction'])->name('index');
            Route::get('crypto_exchanges/edit/{id}', 'CryptoExchangeController@edit')->middleware(['permission:edit_crypto_exchange_transaction'])->name('edit');
            Route::post('crypto_exchanges/update', 'CryptoExchangeController@update')->middleware(['permission:edit_crypto_exchange_transaction'])->name('update');

            Route::get('crypto_exchanges/user_search', 'CryptoExchangeController@exchangesUserSearch')->name('user_search');
            Route::get('crypto_exchanges/csv', 'CryptoExchangeController@exchangeCsv')->name('csv');
            Route::get('crypto_exchanges/pdf', 'CryptoExchangeController@exchangePdf')->name('pdf');
        });

        // Settings
        Route::post('crypto_settings', 'CryptoExchangeSettingController@store')->middleware(['permission:view_crypto_exchange_settings'])->name('admin.crypto_setting_store');
        Route::get('crypto_settings', 'CryptoExchangeSettingController@add')->middleware(['permission:edit_crypto_exchange_settings'])->name('admin.crypto_settings');

    });
});

# Crypto Exchange UnAuthenticated user
Route::group(['middleware' => ['crypto-available:guest_user' ]], function ()
{
    Route::name('guest.crypto_exchange.')->group(function () {
        Route::get('crypto-exchange/create', 'CryptoExchangeController@cryptoExchange')->name('home');
        Route::post('/crypto-exchange/verification', 'CryptoExchangeController@cryptoBuySell')->name('verification');
        Route::get('/crypto-exchange/receiving-info', 'CryptoExchangeController@cryptoBuySellReceive')->name('receiving_info');
        Route::post('/crypto-exchange/receiving-info', 'CryptoExchangeController@receivingInfoStore')->name('store_receiving_info');
        Route::get('/crypto-exchange/payment', 'CryptoExchangeController@cryptoBuySellGateway')->name('gateway');
        Route::get('/crypto-exchange/make-payment', 'CryptoExchangeController@cryptoBuySellPaymentGateway')->name('payment-gateway');
        Route::post('/crypto-exchange/payment', 'CryptoExchangeController@cryptoBuySellPayment')->name('payment');
        Route::post('/crypto-exchange/success', 'CryptoExchangeController@cryptoBuySellSuccess')->name('buy_sell_success');
        Route::get('/crypto-exchange/gateway-success', 'CryptoExchangeController@cryptoPaymentSuccess')->name('payment_success');
        Route::get('crypto-exchange/crypto-phone-verification', 'CryptoExchangeController@generatedPhoneVerificationCode')->name('phone_verification');
        Route::get('crypto-exchange/crypto-email-verification', 'CryptoExchangeController@generatedEmailVerificationCode')->name('email_verification');
        Route::get('crypto-exchange/crypto-phone-verification-complete', 'CryptoExchangeController@completePhoneVerification')->name('phone_verification_success');
        Route::get('crypto-exchange/crypto-email-verification-complete', 'CryptoExchangeController@completeEmailVerification')->name('email_verification_success');
        Route::get('/crypto-exchange/success', 'CryptoExchangeController@cryptoPaymentSuccess')->name('');

        Route::get('/crypto-exchange/complete', 'CryptoExchangeController@successView')->name('view')->withoutMiddleware('crypto-available:guest_user');
    });
});

# Request for Auth & Guest
 Route::name('guest.crypto_exchange.')->group(function () {
        Route::get('crypto-exchange/direction-currencies', 'CryptoExchangeController@directionCurrencies')->name('direction_list');
        Route::get('crypto-exchange/direction-amount', 'CryptoExchangeController@getDirectionAmount')->name('direction_amount');
        Route::get('crypto-exchange/get-direction-by-type', 'CryptoExchangeController@getTabDirection')->name('direction_type');
        Route::get('crypto-exchange/track-transaction/{uuid}', 'CryptoExchangeController@trackTransaction')->name('track_transaction');
 });

# Authenticated User
Route::group(config('addons.route_group.authenticated.user'), function () {
    Route::group(['namespace' => 'Users'],  function () {
        Route::group(['middleware' => ['permission:manage_crypto_exchange', 'check-user-suspended', 'crypto-available:auth_user']], function () {
            Route::get('crypto-exchange/buy-sell', 'CryptoExchangeManualController@exchange')->name('user_dashboard.crypto_buy_sell.create');
            Route::post('crypto-buy-sell-confirm', 'CryptoExchangeManualController@exchangeOfCurrency')->name('user_dashboard.crypto_buy_sell.confirm');
            Route::post('payment-via-gateway', 'CryptoExchangeManualController@paymentViaGateway')->name('user_dashboard.crypto_buy_sell.gateway');
            Route::get('crypto-exchange/confirm', 'CryptoExchangeManualController@paymentConfirm')->name('user_dashboard.crypto_buy_sell.payment_confirm');
            Route::post('exchange-of-crypto-success', 'CryptoExchangeManualController@exchangeOfCurrencyConfirm')->name('user_dashboard.crypto_buy_sell.success');
            Route::get('crypto-buy-sell/success', 'CryptoExchangeManualController@cryptoExchangeSuccess')->name('user_dashboard.crypto_buy_sell.success_page');
            Route::get('crypto-exchange/buy-sell-list', 'CryptoExchangeManualController@exchangeList')->name('user_dashboard.crypto_buy_sell.list');
        });
    });
});

Route::get('crypto-exchange/payment-complete', 'Users\CryptoExchangeManualController@gatewayPaymentComplete')->name('user_dashboard.crypto_buy_sell.gateway_payment');

Route::get('crypto-buy-sell/print/{id}', 'Users\CryptoExchangeManualController@exchangeOfPrintPdf')->name('crypto_exchange.print');

