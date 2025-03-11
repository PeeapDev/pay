<?php

namespace App\Exports;

use App\Models\TransactionCharge;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AdminProfitLogs implements FromArray, WithHeadings{

    public function headings(): array
    {
        return [
            ['SL', 'TRX','USER','USER TYPE','TRANSACTION TYPE','PROFIT AMOUNT','TIME'],
        ];
    }

    public function array(): array
    {
        return TransactionCharge::with('transactions')
        ->whereHas('transactions', function ($query) {
            $query->where('status',1);
        })
        ->latest()->get()->map(function($item,$key){
            if($item->transactions->user_id != null){
                $user_type =  "USER"??"";
            }elseif($item->transactions->agent_id != null){
                $user_type =  "AGENT"??"";
            }elseif($item->transactions->merchant_id != null){
                $user_type =  "MERCHANT"??"";
            }

            if($item->transactions->type == payment_gateway_const()::TYPEADDMONEY){
                $exchange_rate = get_amount($item->transactions->currency->rate ?? get_default_currency_rate(),null,get_wallet_precision());
            }else{
                $exchange_rate = get_amount($item->transactions->creator_wallet->currency->rate ?? get_default_currency_rate(),null,get_wallet_precision());
            }
            $total_charge = $item->total_charge / $exchange_rate;

            return [
                'id'    => $key + 1,
                'trx'  => $item->transactions->trx_id,
                'user'  =>@$item->transactions->creator->fullname,
                'user_type'  =>$user_type,
                'transaction_type'  =>$item->transactions->type == "MONEY-OUT" ? __("WITHDRAW") :  $item->transactions->type,
                'profit_amount'  => get_amount($total_charge,$item->transactions->creator_wallet->currency->code??get_default_currency_code(),get_wallet_precision($item->transactions->creator_wallet->currency)),
                'time'  =>   $item->created_at->format('d-m-y h:i:s A'),
            ];
         })->toArray();

    }
}

