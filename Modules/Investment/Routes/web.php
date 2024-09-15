<?php

# Investment Admin section
Route::group(config('addons.route_group.authenticated.admin'), function () {
    Route::group(['namespace' => 'Admin'], function () {

        // investment plan route start
        Route::get('investment-plans', 'InvestmentPlanController@index')->name('investment_plans.list')->middleware(['permission:view_investment_plan']);
        Route::get('investment-plan/add', 'InvestmentPlanController@add')->name('investment_plan.add')->middleware(['permission:add_investment_plan']);
        Route::post('investment-plan/store', 'InvestmentPlanController@store')->name('investment_plan.store');
        Route::get('investment-plan/edit/{id}', 'InvestmentPlanController@edit')->name('investment_plan.edit')->middleware(['permission:edit_investment_plan']);
        Route::post('investment-plan/update/{id}', 'InvestmentPlanController@update')->name('investment_plan.update');
        Route::get('investment-plan/delete/{id}', 'InvestmentPlanController@delete')->name('investment_plan.delete')->middleware(['permission:delete_investment_plan']);
        Route::post('investment-plan/status', 'InvestmentPlanController@status')->name('investment_plan.status');
        Route::get('investment-plan/get-payment-methods', 'InvestmentPlanController@getPaymentMethods')->name('investment_plan.payment_methods');
        Route::post('investment-plans/view-change', 'InvestmentPlanController@viewChange')->name('investment_plan.view_change');

        // Settings for investment
        Route::get('investment-settings/add', 'InvestmentSettingController@add')->name('investment_setting.add')->middleware(['permission:view_investment_setting']);
        Route::post('investment-settings/store', 'InvestmentSettingController@store')->name('investment_setting.store')->middleware(['permission:edit_investment_setting']);

        //investment routes
        Route::get('investments', 'InvestmentController@index')->name('investment.list')->middleware(['permission:view_investment']);
        Route::get('investments/edit/{id}', 'InvestmentController@edit')->name('investment.edit')->middleware(['permission:edit_investment']);
        Route::post('investment/update', 'InvestmentController@update')->name('investment.update');
        Route::get('investments/details/{id}', 'InvestmentController@details')->name('investment.details')->middleware(['permission:view_investment']);
        Route::get('investment/user-search', 'InvestmentController@investmentsUserSearch')->name('investment.search');
        Route::get('investments/csv', 'InvestmentController@investmentsCsv')->name('investment.csv');
        Route::get('investments/pdf', 'InvestmentController@investmentsPdf')->name('investment.pdf');
        Route::post('investments/approved', 'InvestmentController@approveActiveInvestmentWithdrawal')->name('investments.approved')->middleware(['permission:view_profit_approve']);
        Route::post('investments/update/{id}', 'InvestmentController@updateTransaction')->name('investments.update');
    });
});

# Investment User section


Route::group(config('addons.route_group.authenticated.user'), function () {

    Route::group(['middleware' => ['permission:manage_investment', 'guest:users', 'locale', 'twoFa', 'check-user-inactive'], 'namespace' => 'Users', 'as' => 'user.'], function () {

        //User Investment Plans
        Route::get('investment/plans', 'InvestmentPlanController@index')->name('investment_plans.list');

        //User Invest
        Route::get('invest/get-active-payment-methods', 'InvestController@getActivePaymentMethods')->name('invest.active_payment_methods');
        Route::get('invest/check-currency-type', 'InvestController@checkCurrencyType')->name('invest.check_currency_type');
        Route::get('invest/create', 'InvestController@create')->name('invest.create');
        Route::post('invest/confirm', 'InvestController@store')->name('invest.store');
        Route::post('investment/payment', 'InvestController@payment')->name('invest.payment');
        Route::get('investment/success', 'InvestController@success')->name('invest.success');
        Route::get('investment-list/{status}', 'InvestController@list')->name('investment.list');
        Route::get('investment-details/{id}', 'InvestController@detail')->name('investment.details');
        Route::post('invest/check-invest-user-amount-limit', 'InvestController@checkInvestmentUserAmountLimit')->name('invest.check_amount_limit');
        Route::get('investment-money/print/{id}', 'InvestController@investmentPrintPdf')->name('investment-money.print');

    });
});

Route::get('invest/success-payment', 'Users\InvestController@successPayment')->name('user.investment.successPayment');

// job queue process route
Route::get('investment/process-queue-emails', 'JobQueueProcessController');

