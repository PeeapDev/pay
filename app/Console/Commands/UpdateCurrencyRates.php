<?php

namespace App\Console\Commands;

use App\Constants\GlobalConst;
use App\Http\Helpers\CurrencyLayer;
use App\Models\Admin\Currency;
use App\Models\Admin\PaymentGatewayCurrency;
use App\Models\LiveExchangeRateApiSetting;
use Illuminate\Console\Command;
use Exception;

class UpdateCurrencyRates extends Command
{
    protected $signature = 'currency:update';
    protected $description = 'Update currency rates using CurrencyLayer API';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $api_rates = (new CurrencyLayer())->getLiveExchangeRates();

            if (isset($api_rates) && $api_rates['status'] == false) {
                info($api_rates['message'] ?? "Something went wrong! Please try again.");
                return;
            }

            $api_rates = $api_rates['data'];
            $provider = LiveExchangeRateApiSetting::where('slug', GlobalConst::CURRENCY_LAYER)->first();

            // For Setup Currency Rate Update
            if ($provider->currency_module == 1) {
                $currencies = Currency::active()->get();
                foreach ($currencies as $currency) {
                    if (array_key_exists($currency->code, $api_rates)) {
                        $currency->rate = $api_rates[$currency->code];
                        $currency->save();
                    }
                }
            }

            // For Gateway Currency Rate Update
            if ($provider->payment_gateway_module == 1) {
                $payment_gateways_currencies = PaymentGatewayCurrency::whereHas('gateway', function ($gateway) {
                    $gateway->where('status', 1);
                })->get();
                foreach ($payment_gateways_currencies as $currency) {
                    if (array_key_exists($currency->currency_code, $api_rates)) {
                        $currency->rate = $api_rates[$currency->currency_code];
                        $currency->save();
                    }
                }
            }

            info('Currency Rate Updated Successfully by Currency Layer.');

        } catch (Exception $e) {
            info($e->getMessage()??"Something went wrong! Please try again.");
        }
    }
}
