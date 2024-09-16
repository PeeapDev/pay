<?php

namespace Modules\CryptoExchange\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\EmailController;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Helpers\Common;
use Illuminate\Http\Request;
use App\Models\{
    Transaction,
    Wallet
};
use Modules\CryptoExchange\Datatables\CryptoExchangesDataTable;
use Modules\CryptoExchange\Exports\CryptoExchangesExport;
use Modules\CryptoExchange\Entities\{
    CryptoExchange, 
    CryptoExchangeTransaction
};
use Modules\CryptoExchange\Services\Mail\CryptoStatusChangeToUserMailService;
use Modules\CryptoExchange\Services\Sms\CryptoStatusChangeToUserSmsService;

class CryptoExchangeController extends Controller
{
    protected $helper;
    protected $cryptoExchange;
    protected $email;


    public function __construct()
    {
        $this->helper = new Common();
        $this->cryptoExchange = new CryptoExchange();
        $this->email = new EmailController();
    }

    public function index(CryptoExchangesDataTable $dataTable)
    {
        if (!m_g_c_v('Q1JZUFRPRVhDSEFOR0VfU0VDUkVU') && m_aic_c_v('Q1JZUFRPRVhDSEFOR0VfU0VDUkVU')) {
            return view('addons::install', ['module' => 'Q1JZUFRPRVhDSEFOR0VfU0VDUkVU']);
        }

        $data = [];
        $data['menu'] = 'crypto_exchange';
        $data['sub_menu'] = 'crypto_exchanges';
        $data['exchanges_status'] = $this->cryptoExchange->select('status')->groupBy('status')->get();
        $data['exchanges_currency'] = CryptoExchange::with('fromCurrency', 'toCurrency')->groupBy('from_currency')->get();
        $data['from'] = isset(request()->from) ? setDateForDb(request()->from) : null;
        $data['to'] = isset(request()->to ) ? setDateForDb(request()->to) : null;
        $data['status'] = isset(request()->status) ? request()->status : 'all';
        $data['currency'] = isset(request()->currency) ? request()->currency : 'all';
        $data['user'] = $user = isset(request()->user_id) ? request()->user_id : null;
        $data['getName'] = $this->cryptoExchange->getExchangesUserName($user);
        return $dataTable->render('cryptoexchange::admin.crypto_exchange.list', $data);
    }

    public function exchangesUserSearch(Request $request)
    {
        $search = $request->search;
        $user = $this->cryptoExchange->getExchangesUsersResponse($search);
        $res = [
            'status' => 'fail',
        ];
        if (count($user) > 0) {
            $res = [
                'status' => 'success',
                'data'   => $user,
            ];
        }
        return json_encode($res);
    }

    public function exchangeCsv()
    {
        return Excel::download(new CryptoExchangesExport(), 'crypto_exchanges_list_' . time() . '.xlsx');
    }

    public function exchangePdf()
    {
        $from = !empty(request()->startfrom) ? setDateForDb(request()->startfrom) : null;
        $to = !empty(request()->endto) ? setDateForDb(request()->endto) : null;
        $status = isset(request()->status) ? request()->status : null;
        $currency = isset(request()->currency) ? request()->currency : null;
        $user = isset(request()->user_id) ? request()->user_id : null;
        $crypto_exchanges = $this->cryptoExchange->getExchangesList($from, $to, $status, $currency, $user)->get();
        $data = [];
        $data['crypto_exchanges'] = $crypto_exchanges = collect($crypto_exchanges)->sortByDesc('id');
        if (isset($from) && isset($to)) {
            $data['date_range'] = $from . ' To ' . $to;
        } else {
            $data['date_range'] = 'N/A';
        }
        generatePDF('cryptoexchange::admin.crypto_exchange.exchanges_report_pdf', 'crypto_exchanges_report_', $data);
    }

    public function edit($id)
    {
        $data = [];
        $exchange = CryptoExchange::find($id);
        $transaction = Transaction::select('id', 'transaction_type_id', 'status', 'transaction_reference_id', 'percentage', 'charge_percentage', 'charge_fixed', 'uuid', 'phone','bank_id','file_id')
            ->where(['transaction_reference_id' => $exchange->id, 'uuid' => $exchange->uuid])
            ->whereIn('transaction_type_id', [Crypto_Buy, Crypto_Sell, Crypto_Swap])
            ->first();
        $data = (new CryptoExchangeTransaction())->getTransactionDetails($transaction->id);
        $data['menu'] = 'crypto_exchange';
        $data['sub_menu'] = 'crypto_exchanges';
        return view('cryptoexchange::admin.transaction.edit', $data);
    }

    public function update(Request $request)
    {
        try {


            $cryptoExchange = CryptoExchange::find($request->transaction_reference_id);
            if ($request->status == $cryptoExchange->status) {
                $this->helper->one_time_message('error', __('Transaction is already :x', ['x' => $request->status]));
                return redirect($request->redirect);
            }
            if ($request->status == 'Pending') {
                $this->helper->one_time_message('error', __('Status not changed'));
                return redirect($request->redirect);
            }
            $to_wallet = Wallet::where([
                'user_id'     => $cryptoExchange->user_id,
                'currency_id' => $cryptoExchange->to_currency,
            ])->select('balance')->first();

            $from_wallet = Wallet::where([
                'user_id'     => $cryptoExchange->user_id,
                'currency_id' => $cryptoExchange->from_currency,
            ])->select('balance')->first();

            if ($request->status == 'Success') {
                if ($cryptoExchange->status == 'Pending') {
                    if ($cryptoExchange->receive_via == 'wallet') {
                        if (!$to_wallet) {
                            $to_wallet = Wallet::createWallet($cryptoExchange->user_id, $cryptoExchange->to_currency);
                        }
                        Wallet::where([
                            'user_id'     => $cryptoExchange->user_id,
                            'currency_id' => $cryptoExchange->to_currency,
                        ])->update([
                            'balance' => $to_wallet->balance + $cryptoExchange->get_amount,
                        ]);
                    }
                } elseif ($cryptoExchange->status == 'Blocked') {
                    if ($cryptoExchange->receive_via == 'wallet') {
                        if (!$to_wallet) {
                            $to_wallet = Wallet::createWallet($cryptoExchange->user_id, $cryptoExchange->to_currency);
                        }

                        Wallet::where([
                            'user_id'     => $cryptoExchange->user_id,
                            'currency_id' => $cryptoExchange->to_currency,
                        ])->update([
                            'balance' => $to_wallet->balance + $cryptoExchange->get_amount,
                        ]);
                    }
                    if ($cryptoExchange->send_via == 'wallet') {
                        Wallet::where([
                            'user_id'     => $cryptoExchange->user_id,
                            'currency_id' => $cryptoExchange->from_currency,
                        ])->update([
                            'balance' => $from_wallet->balance - $cryptoExchange->amount,
                        ]);
                    }
                }
            } elseif ($request->status == 'Blocked') {
                if ($cryptoExchange->status == 'Success') {
                    if ($cryptoExchange->receive_via == 'wallet') {
                        Wallet::where([
                            'user_id'     => $cryptoExchange->user_id,
                            'currency_id' => $cryptoExchange->to_currency,
                        ])->update([
                            'balance' => $to_wallet->balance - $cryptoExchange->get_amount,
                        ]);
                    }
                    if ($cryptoExchange->send_via == 'wallet') {
                        Wallet::where([
                            'user_id'     => $cryptoExchange->user_id,
                            'currency_id' => $cryptoExchange->from_currency,
                        ])->update([
                            'balance' => $from_wallet->balance + $cryptoExchange->amount,
                        ]);
                    }
                } elseif ($cryptoExchange->status == 'Pending') {
                    if ($cryptoExchange->send_via == 'wallet') {
                        Wallet::where([
                            'user_id'     => $cryptoExchange->user_id,
                            'currency_id' => $cryptoExchange->from_currency,
                        ])->update([
                            'balance' => $from_wallet->balance + $cryptoExchange->amount,
                        ]);
                    }
                }
            }

            //Transferred entry update
            Transaction::where([
                'user_id'                  => $request->user_id,
                'transaction_reference_id' => $request->transaction_reference_id,
                'transaction_type_id'      => $request->transaction_type_id,
            ])->update([
                'payment_status' => 'Success',
                'status' => $request->status,
            ]);
            $cryptoExchange->status = $request->status;
            $cryptoExchange->save();

            //Email - Crypto Exchange
            $data = [
                'amount' => moneyFormat(optional($cryptoExchange->toCurrency)->symbol, formatNumber($cryptoExchange->get_amount, $cryptoExchange->to_currency)),
                'action' => $request->status == 'Success' ? 'added' : 'subtracted',
                'fromTo' => 'to',
                'type' => $request->type,
                'user' => $cryptoExchange->user
            ];

            if (!is_null($cryptoExchange->user_id)) {
                $data['email'] = $cryptoExchange?->user?->email;
                (new CryptoStatusChangeToUserMailService)->send($cryptoExchange, $data);
            } elseif ($cryptoExchange->verification_via == 'email') {
                $data['email'] = $cryptoExchange->email_phone;
                (new CryptoStatusChangeToUserMailService)->send($cryptoExchange, $data);
            }

            if (!empty($cryptoExchange?->user?->formattedPhone)) {
                $data['phone'] = $cryptoExchange?->user?->formattedPhone;
                (new CryptoStatusChangeToUserSmsService)->send($cryptoExchange, $data);
            } else if ($cryptoExchange->verification_via == 'phone') {
                $data['phone'] = $cryptoExchange->email_phone;
                (new CryptoStatusChangeToUserSmsService)->send($cryptoExchange, $data);
            }

            $this->helper->one_time_message('success', __('Transaction updated successfully'));
            return redirect($request->redirect);
        } catch (\Exception $e) {
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect($request->redirect);
        }
    }
}
