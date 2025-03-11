<?php

namespace App\Http\Helpers;

use App\Constants\PaymentGatewayConst;
use App\Models\Transaction;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class TransactionLimit{
    public function getExchangeRate($fromCurrency)
    {
        $base_currency_rate     = get_default_currency_rate();
        $from_currency_rate     = $fromCurrency->rate ?? 1;
        $exchange_rate          = $from_currency_rate / $base_currency_rate;

        return $exchange_rate;
    }
    function trxLimit($user_field,$userId,$transactionType,$currency,$amount,$limits,$attribute,$json = null){

        $transactionDate = now();
        //  Get the limits for the specified transaction type
        if($transactionType == PaymentGatewayConst::TYPEMONEYOUT || $transactionType == PaymentGatewayConst::TYPEADDMONEY){
            $exchange_rate =  $limits->rate/$currency->rate;

            $dailyLimitBaseCurrency     = get_amount($limits->daily_limit / $exchange_rate ,null,get_wallet_precision($currency));  // in Base Currency
            $monthlyLimitBaseCurrency   = get_amount($limits->monthly_limit / $exchange_rate ,null,get_wallet_precision($currency)); // in Base Currency

            //make convert to base curr
            $reverse_exchange_rate_base = get_amount(( get_default_currency_rate() / $currency->rate),null,get_wallet_precision($currency));

            $dailyLimitBaseCurrency     = get_amount(( $dailyLimitBaseCurrency *  $reverse_exchange_rate_base),null,get_wallet_precision($currency));   // in Selected Currency
            $monthlyLimitBaseCurrency   = get_amount(($monthlyLimitBaseCurrency *  $reverse_exchange_rate_base),null,get_wallet_precision($currency));  // in Selected Currency


            $dailyLimitSelectedCurrency     = get_amount(( $limits->daily_limit / $exchange_rate),null,get_wallet_precision($currency));  // in Selected Currency
            $monthlyLimitSelectedCurrency   = get_amount(($limits->monthly_limit / $exchange_rate),null,get_wallet_precision($currency)); // in Selected Currency


        }else{
            $dailyLimitBaseCurrency     = get_amount($limits->daily_limit,null,get_wallet_precision());  // in Base Currency
            $monthlyLimitBaseCurrency   = get_amount($limits->monthly_limit,null,get_wallet_precision()); // in Base Currency

            $dailyLimitSelectedCurrency     = get_amount(( $dailyLimitBaseCurrency * $currency->rate),null,get_wallet_precision($currency));  // in Selected Currency
            $monthlyLimitSelectedCurrency   = get_amount(($monthlyLimitBaseCurrency * $currency->rate),null,get_wallet_precision($currency)); // in Selected Currency
        }

        $exchangeRate = get_amount($this->getExchangeRate($currency),null,get_wallet_precision($currency)); // This function should return the conversion rate for currency to Base Currency

        if (!$exchangeRate) {
            throw new Exception(json_encode([
                'status' => false,
                'message' => 'Exchange rate not found',
                'user_field' => $user_field,
                'user_id' => $userId,
                'transaction_type' => $transactionType
            ]));
        }


        // Convert the transaction amount to the base currency (Base Currency)
        $amountInBasedCurrency = get_amount(($amount / $exchangeRate ),null,get_wallet_precision($currency));


        $dailyTotals = Transaction::where($user_field, $userId)
                            ->where('type', $transactionType)
                            ->where('attribute',$attribute)
                            ->where('status',PaymentGatewayConst::STATUSSUCCESS)
                            ->whereDate('created_at', Carbon::parse($transactionDate)->toDateString())
                            ->get();


        $monthlyTotals= Transaction::where($user_field, $userId)
                            ->where('type', $transactionType)
                            ->where('attribute',$attribute)
                            ->where('status',PaymentGatewayConst::STATUSSUCCESS)
                            ->whereYear('created_at', Carbon::parse($transactionDate)->year)
                            ->whereMonth('created_at', Carbon::parse($transactionDate)->month)
                            ->get();

        // Calculate the daily & monthly total for the transaction type in the base currency (Base Currency)
        $dailyTotalInBasedCurrency      = get_amount($this->getTransactionOnBaseCurrency($dailyTotals),null,get_wallet_precision($currency));
        $monthlyTotalInBasedCurrency    = get_amount($this->getTransactionOnBaseCurrency($monthlyTotals),null,get_wallet_precision($currency));

         // Calculate the daily & monthly total for the transaction type in the selected currency (Selected Currency)
        $totalInCurrencyDaily = get_amount(($dailyTotalInBasedCurrency * $currency->rate), null, get_wallet_precision($currency));
        $totalInCurrencyMonthly = get_amount(($monthlyTotalInBasedCurrency * $currency->rate), null, get_wallet_precision($currency));

         // Calculate the remaining  daily & monthly total for the transaction type in the selected currency (Selected Currency)
        $totalRemainingDaily = get_amount(($dailyLimitSelectedCurrency - $totalInCurrencyDaily), null, get_wallet_precision($currency));
        $totalRemainingMonthly = get_amount(($monthlyLimitSelectedCurrency - $totalInCurrencyMonthly), null, get_wallet_precision($currency));

        $totalRemainingDaily  = $totalRemainingDaily <= 0 ? 0 :$totalRemainingDaily;
        $totalRemainingMonthly  = $totalRemainingMonthly <= 0 ? 0 :$totalRemainingMonthly;

        $data =[
            'totalDailyTxnBase'             => $dailyTotalInBasedCurrency ?? 0,
            'totalMonthlyTxnBase'           => $monthlyTotalInBasedCurrency ?? 0,

            'totalDailyTxnSelected'         => $totalInCurrencyDaily ?? 0,
            'totalMonthlyTxnSelected'       => $totalInCurrencyMonthly ?? 0,

            'remainingDailyTxnSelected'     => $totalRemainingDaily ?? 0,
            'remainingMonthlyTxnSelected'   => $totalRemainingMonthly ?? 0


        ];

        // Validate daily and monthly limits
        if ($dailyLimitBaseCurrency > 0 && ($dailyTotalInBasedCurrency + $amountInBasedCurrency) > $dailyLimitBaseCurrency) {

            if($json != null){
                return[
                    'status'            => false,
                    'message'           => __('Daily transaction limit exceeded.'),
                    'user_field'        => $user_field,
                    'user_id'           => $userId,
                    'transaction_type'  => $transactionType,
                    'data'              => $data,
                ];

            }else{

                throw new Exception(json_encode([
                    'status'            => false,
                    'message'           => __('Daily transaction limit exceeded.'),
                    'user_field'        => $user_field,
                    'user_id'           => $userId,
                    'transaction_type'  => $transactionType,
                    'data'              =>  $data,
                ]));
            }

        }

        if ($monthlyLimitBaseCurrency > 0 && ($monthlyTotalInBasedCurrency + $amountInBasedCurrency) > $monthlyLimitBaseCurrency) {
            if($json != null){
                return[
                    'status'            => false,
                    'message'           => __('Monthly transaction limit exceeded.'),
                    'user_field'        => $user_field,
                    'user_id'           => $userId,
                    'transaction_type'  => $transactionType,
                    'data'              => $data,
                ];

            }else{
                throw new Exception(json_encode([
                    'status'                => false,
                    'message'               => __('Monthly transaction limit exceeded.'),
                    'user_field'            => $user_field,
                    'user_id'               => $userId,
                    'transaction_type'      => $transactionType,
                    'data'                  => $data,
                ]));
            }

        }


        return [
            'status'                => true,
            'message'               => __('Your current total transaction amount'),
            'user_field'            => $user_field,
            'user_id'               => $userId,
            'transaction_type'      => $transactionType,
            'data'                  => $data,

        ];
    }

    public function getTransactionOnBaseCurrency($transactions){
        $totalAmount = 0;
        foreach ($transactions as $transaction) {
            $requestAmount = $transaction->request_amount;
            if($transaction->type == PaymentGatewayConst::VIRTUALCARD){
                $exchange_rate = $transaction->details->charges->card_currency_rate??get_default_currency_rate();
            }else{
                $exchange_rate = $transaction->creator_wallet->currency->rate??get_default_currency_rate();
            }
            $result = $requestAmount / $exchange_rate;
            $totalAmount += $result;
        }
        return $totalAmount??0 ;

    }
}
