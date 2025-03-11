<?php

namespace App\Exports;

use App\Constants\PaymentGatewayConst;
use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RemittanceTransactionExport implements FromArray, WithHeadings{

    public function headings(): array
    {
        return [
            ['SL', 'TRX','SENDER','RECEIVER','REMITTANCE TYPE','SEND AMOUNT','STATUS','TIME'],
        ];
    }

    public function array(): array
    {
        return Transaction::with(
            'user:id,firstname,lastname,email,username,full_mobile',
              'currency:id,name',
          )->where('type', PaymentGatewayConst::SENDREMITTANCE)->latest()->get()->map(function($item,$key){

            if($item->attribute == "SEND"){
                if($item->user_id != null){
                    $sender =  $item->user->fullname;
                }else{
                    $sender =  $item->agent->fullname;
                }

            }else{
                if($item->user_id != null){
                    $sender = $item->details->sender->fullname;
                }else{
                    $sender = $item->details->sender_recipient->fullname;
                }

            }
            if($item->attribute == "RECEIVED"){
                if($item->user_id != null){
                    $receiver =   $item->user->fullname;
                }else{
                    $receiver =   $item->agent->fullname;
                }

            }else{
                if($item->user_id != null){
                    $receiver = @$item->details->receiver->firstname.' '.@$item->details->receiver->lastname;
                }else{
                    $receiver = @$item->details->receiver_recipient->firstname.' '.@$item->details->receiver_recipient->lastname;
                }

            }


            return [
                'id'    => $key + 1,
                'trx'  => $item->trx_id,
                'sender'  => $sender,
                'receiver'  => $receiver,
                'remittance_type'  => ucwords(str_replace('-', ' ', @$item->details->remitance_type)),
                'amount'  =>   get_amount($item->request_amount,$item->details->charges->sender_cur_code??get_default_currency_code(),get_wallet_precision($item->creator_wallet->currency)),
                'status'  => __( $item->stringStatus->value),
                'time'  =>   $item->created_at->format('d-m-y h:i:s A'),
            ];
         })->toArray();

    }
}

