<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use App\Models\TransactionType;
use App\Models\PaymentMethod;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default string length for database columns
        Schema::defaultStringLength(191);

        // Clear the X-Powered-By header for security reasons
        header('x-powered-by:');

        if (env('APP_INSTALL') == true) {
            
            $transactionTypes = TransactionType::all()->toArray();

            foreach ($transactionTypes as $transactionType) {
                if (!defined($transactionType['name'])) {
                    define($transactionType['name'], $transactionType['id']);
                }
            }

            $paymentMethods = PaymentMethod::all()->toArray();

            foreach ($paymentMethods as $paymentMethod) {
                if (!defined($paymentMethod['name'])) {
                    define($paymentMethod['name'], $paymentMethod['id']);
                }
            }

            $adminUrlPrefix = preference('admin_url_prefix');
            if (!empty($adminUrlPrefix)) {
                Config::set(['adminPrefix' => $adminUrlPrefix]);
                View::share('adminPrefix', $adminUrlPrefix);
            }
        }
    }
}
