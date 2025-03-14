<?php

namespace App\Exports;

use App\Constants\PaymentGatewayConst;
use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AgentMoneyOutTrxExport implements FromArray, WithHeadings{

    public function headings(): array
    {
        return [
            ['SL', 'TRX','SENDER TYPE','SENDER','RECEIVER TYPE','RECEIVER','SENDER AMOUNT','RECEIVER AMOUNT','CHARGE','PAYABLE','STATUS','TIME'],
        ];
    }

    public function array(): array
    {
        return Transaction::with(
            'user:id,firstname,lastname,email,username,full_mobile',
              'currency:id,name',
          )->where('type', PaymentGatewayConst::AGENTMONEYOUT)->where('attribute',PaymentGatewayConst::SEND)->latest()->get()->map(function($item,$key){
            return [
                'id'    => $key + 1,
                'trx'   => $item->trx_id,
                'sender_type'=> "USER",
                'sender'  => $item->creator->email,
                'receiver_type'  => "AGENT",
                'receiver'  =>$item->details->receiver_email,
                'sender_amount'  =>  get_amount($item->details->charges->sender_amount,$item->details->charges->sender_currency,get_wallet_precision($item->creator_wallet->currency)),
                'receiver_amount'  =>get_amount($item->details->charges->receiver_amount,$item->details->charges->receiver_currency,$item->details->charges->r_precision_digit??2),
                'charge_amount'  =>  get_amount($item->details->charges->total_charge,$item->details->charges->sender_currency,get_wallet_precision($item->creator_wallet->currency)),
                'payable_amount'  => get_amount($item->details->charges->payable,$item->details->charges->sender_currency,get_wallet_precision($item->creator_wallet->currency)),
                'status'  => __( $item->stringStatus->value),
                'time'  =>   $item->created_at->format('d-m-y h:i:s A'),
            ];
         })->toArray();

    }
}

