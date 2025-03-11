<?php

namespace App\Exports;

use App\Constants\PaymentGatewayConst;
use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExchangeMoneyTrxExport implements FromArray, WithHeadings{

    public function headings(): array
    {
        return [
            ['SL', 'TRX','USER TYPE','USER EMAIL','FROM COUNTRY','TO COUNTRY','EXCHANGE AMOUNT','EXCHANGE RATE','EXCHANGEABLE AMOUNT','CHARGE','PAYABLE','STATUS','TIME'],
        ];
    }

    public function array(): array
    {
        return Transaction::with(
            'user:id,firstname,lastname,email,username,full_mobile',
              'currency:id,name',
          )->where('type', PaymentGatewayConst::TYPEMONEYEXCHANGE)->where('attribute',PaymentGatewayConst::SEND)->latest()->get()->map(function($item,$key){
            if($item->user_id != null){
                $user_type =  "USER"??"";
            }elseif($item->agent_id != null){
                $user_type =  "AGENT"??"";
            }elseif($item->merchant_id != null){
                $user_type =  "MERCHANT"??"";
            }
            return [
                'id'                    => $key + 1,
                'trx'                   => $item->trx_id,
                'user_type'             => $user_type,
                'user_email'            => $item->creator->email,
                'from_country'          => $item->details->charges->from_wallet_country,
                'to_country'            => $item->details->charges->to_wallet_country,
                'exchange_amount'       => get_amount($item->details->charges->request_amount,$item->details->charges->request_currency,get_wallet_precision($item->creator_wallet->currency)),
                'exchange_rate'         => get_amount(1,$item->details->charges->request_currency) ." = ".get_amount($item->details->charges->exchange_rate,$item->details->charges->exchange_currency,$item->details->charges->r_precision_digit??2),
                'exchangeable_amount'   => get_amount($item->details->charges->exchange_amount,$item->details->charges->exchange_currency,$item->details->charges->r_precision_digit??2),
                'charge_amount'         => get_amount($item->charge->total_charge,$item->details->charges->request_currency,get_wallet_precision($item->creator_wallet->currency)),
                'payable_amount'        => get_amount($item->details->charges->payable,$item->details->charges->request_currency,get_wallet_precision($item->creator_wallet->currency)),
                'status'                => __( $item->stringStatus->value),
                'time'                  =>  $item->created_at->format('d-m-y h:i:s A'),
            ];
         })->toArray();

    }
}

