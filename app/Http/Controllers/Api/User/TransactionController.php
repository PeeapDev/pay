<?php

namespace App\Http\Controllers\Api\User;

use App\Models\Transaction;
use App\Http\Controllers\Controller;
use App\Constants\PaymentGatewayConst;
use App\Http\Helpers\Api\Helpers;
use App\Http\Resources\User\AddMoneyLogs;
use App\Http\Resources\User\AddSubBalanceLogs;
use App\Http\Resources\User\AgentMoneyOutLogs;
use App\Http\Resources\User\BillPayLogs;
use App\Http\Resources\User\ExchangeMoneyLogs;
use App\Http\Resources\User\GiftCardLogs;
use App\Http\Resources\User\MakePaymentLogs;
use App\Http\Resources\User\MerchantPaymentLogs;
use App\Http\Resources\User\MobileTopupLogs;
use App\Http\Resources\User\MoneyInLogs;
use App\Http\Resources\User\MoneyOutLogs;
use App\Http\Resources\User\PayLinkResource;
use App\Http\Resources\User\PaymentPayLinkResource;
use App\Http\Resources\User\ReferBonus;
use App\Http\Resources\User\RegistrationBonus;
use App\Http\Resources\User\RemittanceLogs;
use App\Http\Resources\User\RequestMoneyLogs;
use App\Http\Resources\User\TransferMoneyLogs;
use App\Http\Resources\User\VirtualCardLogs;
use Exception;

class TransactionController extends Controller
{


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($slug = null) {

        // start transaction now
        $bill_pay           = Transaction::auth()->billPay()->orderByDesc("id")->get();
        $mobileTopUp        = Transaction::auth()->mobileTopup()->orderByDesc("id")->get();
        $addMoney           = Transaction::auth()->addMoney()->orderByDesc("id")->latest()->get();
        $moneyOut           = Transaction::auth()->moneyOut()->orderByDesc("id")->get();
        $sendMoney          = Transaction::auth()->senMoney()->orderByDesc("id")->get();
        $exchangeMoney      = Transaction::auth()->exchangeMoney()->orderByDesc("id")->get();
        $moneyIn            = Transaction::auth()->moneyIn()->orderByDesc("id")->get();
        $agentMoneyOut      = Transaction::auth()->agentMoneyOut()->orderByDesc("id")->get();
        $requestMoney       = Transaction::auth()->requestMoney()->orderByDesc("id")->get();
        $payLink            = Transaction::auth()->payLink()->where('attribute',PaymentGatewayConst::RECEIVED)->orderByDesc('id')->get();
        $payLinkPaymentByUser= Transaction::auth()->payLink()->where('attribute',PaymentGatewayConst::SEND)->orderByDesc('id')->get();
        $virtualCard        = Transaction::auth()->virtualCard()->orderByDesc("id")->get();
        $remittance         = Transaction::auth()->remitance()->orderByDesc("id")->get();
        $merchant_payment   = Transaction::auth()->merchantPayment()->orderByDesc("id")->get();
        $make_payment       = Transaction::auth()->makePayment()->orderByDesc("id")->get();
        $giftCards          = Transaction::auth()->giftCards()->orderByDesc("id")->get();
        $addSubBalance      = Transaction::auth()->addSubBalance()->orderByDesc("id")->get();
        $refer_bonus        = Transaction::auth()->referBonus()->orderByDesc("id")->get();
        $register_bonus     = Transaction::auth()->registerBonus()->orderByDesc("id")->get();


        $transactions = [
            'bill_pay'          => BillPayLogs::collection($bill_pay),
            'mobile_top_up'     => MobileTopupLogs::collection($mobileTopUp),
            'add_money'         => AddMoneyLogs::collection($addMoney),
            'money_out'         => MoneyOutLogs::collection($moneyOut),
            'send_money'        => TransferMoneyLogs::collection($sendMoney),
            'exchange_money'    => ExchangeMoneyLogs::collection($exchangeMoney),
            'money_in'          => MoneyInLogs::collection($moneyIn),
            'agent_money_out'   => AgentMoneyOutLogs::collection($agentMoneyOut),
            'request_money'     => RequestMoneyLogs::collection($requestMoney),
            'virtual_card'      => VirtualCardLogs::collection($virtualCard),
            'pay_link'          => PayLinkResource::collection($payLink),
            'pay_user_pay_link' => PaymentPayLinkResource::collection($payLinkPaymentByUser),
            'remittance'        => RemittanceLogs::collection($remittance),
            'merchant_payment'  => MerchantPaymentLogs::collection($merchant_payment),
            'make_payment'      => MakePaymentLogs::collection($make_payment),
            'gift_cards'        => GiftCardLogs::collection($giftCards),
            'add_sub_balance'   => AddSubBalanceLogs::collection($addSubBalance),
            'refer_bonus'       => ReferBonus::collection($refer_bonus),
            'register_bonus'    => RegistrationBonus::collection($register_bonus),
        ];
        $transactions = (object)$transactions;

        $transaction_types = [
            'add_money'         => PaymentGatewayConst::TYPEADDMONEY,
            'money_out'         => PaymentGatewayConst::TYPEMONEYOUT,
            'transfer_money'    => PaymentGatewayConst::TYPETRANSFERMONEY,
            'exchange_money'    => PaymentGatewayConst::TYPEMONEYEXCHANGE,
            'money_in'          => PaymentGatewayConst::MONEYIN,
            'agent_money_out'   => PaymentGatewayConst::AGENTMONEYOUT,
            'request_money'     => PaymentGatewayConst::REQUESTMONEY,
            'pay_link'          => PaymentGatewayConst::TYPEPAYLINK,
            'pay_user_pay_link' => PaymentGatewayConst::PAYMENTPAYLINK,
            'bill_pay'          => PaymentGatewayConst::BILLPAY,
            'mobile_top_up'     => PaymentGatewayConst::MOBILETOPUP,
            'virtual_card'      => PaymentGatewayConst::VIRTUALCARD,
            'remittance'        => PaymentGatewayConst::SENDREMITTANCE,
            'merchant-payment'  => PaymentGatewayConst::MERCHANTPAYMENT,
            'make_payment'      => PaymentGatewayConst::TYPEMAKEPAYMENT,
            'gift_cards'        => PaymentGatewayConst::GIFTCARD,
            'add_sub_balance'   => PaymentGatewayConst::TYPEADDSUBTRACTBALANCE,
            'refer_bonus'       => PaymentGatewayConst::TYPEREFERBONUS,
            'register_bonus'    => PaymentGatewayConst::TYPEBONUS,

        ];
        $transaction_types = (object)$transaction_types;
        $data =[
            'transaction_types' => $transaction_types,
            'transactions'=> $transactions,
        ];
        $message =  ['success'=>[__('All Transactions')]];
        return Helpers::success($data,$message);
    }

}
