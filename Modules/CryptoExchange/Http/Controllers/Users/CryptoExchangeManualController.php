<?php

namespace Modules\CryptoExchange\Http\Controllers\Users;

use App\Http\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Modules\CryptoExchange\Entities\CryptoExchange;
use Modules\CryptoExchange\Http\Requests\{CryptoUserRequest,
    CryptoExchangeRequest
};
use Modules\CryptoExchange\Services\CryptoExchangeService;

class CryptoExchangeManualController extends Controller
{
    protected $helper;
    protected $service;

    public function __construct()
    {
        $this->helper = new Common();
        $this->service = new CryptoExchangeService();
    }

    public function exchangeList()
    {
        $cryptoExchangeList = CryptoExchange::get();
        $data['from'] = isset(request()->from) ? setDateForDb(request()->from) : null;
        $data['to'] = isset(request()->to) ? setDateForDb(request()->to) : null;
        $data['type'] = isset(request()->type) ? request()->type : 'all';
        $data['status'] = isset(request()->status) ? request()->status : 'all';
        $data['currency'] = isset(request()->currency) ? request()->currency : 'all';
        $data['user'] = auth()->id();

        $cryptoExchanges =  (new CryptoExchange())->getExchangesList($data['from'], $data['to'], $data['status'], 'all', $data['user']);
        $data['statuses'] = $cryptoExchangeList->pluck('status')->unique();
        $data['types'] = $cryptoExchangeList->pluck('type')->unique();
        $data['currencies'] = Currency::where('type', 'Crypto')->get();

        if (!empty($data['type']) && $data['type'] != 'all') {
            $data['cryptoExchanges'] = $cryptoExchanges->where('type', $data['type'])->orderByDesc('id')->paginate(10);
        } else {
            $data['cryptoExchanges'] = $cryptoExchanges->orderByDesc('id')->paginate(10);
        }

        if (!empty($data['currency']) && $data['currency'] != 'all') {
            $data['cryptoExchanges'] = $cryptoExchanges->where('from_currency', $data['currency'])->orWhere('to_currency', $data['currency'])->orderByDesc('id')->paginate(10);
        } else {
            $data['cryptoExchanges'] = $cryptoExchanges->orderByDesc('id')->paginate(10);
        }

        return view('cryptoexchange::user.transaction.list', $data);
    }

    public function exchange()
    {
        if (!m_g_c_v('Q1JZUFRPRVhDSEFOR0VfU0VDUkVU') && m_aic_c_v('Q1JZUFRPRVhDSEFOR0VfU0VDUkVU')) {
            return view('vendor.installer.errors.user');
        }

        setActionSession();

        $data = [];

        $exchange_type = (preference('transaction_type') == 'crypto_buy_sell') ? 'crypto_buy' : 'crypto_swap';

        if (route('user_dashboard.crypto_buy_sell.payment_confirm') == url()->previous()) {
            $paymentData = getPaymentData();
            $exchange_type = $paymentData['exchange_type'];
        }

        $data = [
            'icon' => 'money',
            'menu' => 'Crypto Exchange',
            'content_title' => 'Crypto Exchange',
            'exchangeType' => $exchange_type,
        ];

        try {
            $fromCurrency = isset($paymentData) ? $paymentData['from_currency'] : null;
            $data = array_merge($data, $this->service->getCryptoExchangeDirection($exchange_type, $fromCurrency));
            $data['min_amount'] = isset($paymentData) ? $paymentData['send_amount'] : $data['min_amount'];
            $data['selectedTo'] = isset($paymentData) ? $paymentData['to_currency'] : '';
            $data['selectedFrom'] =  isset($paymentData) ? $paymentData['from_currency'] : '';

        } catch (Exception $e) {
            $this->helper->one_time_message('error', $e->getMessage());
        }

        return view('cryptoexchange::user.crypto-exchange.create', $data);
    }


    public function exchangeOfCurrency(CryptoExchangeRequest $request)
    {
        actionSessionCheck();

        $data = [];
        try {
            $data = $this->service->confirmCryptoExchange(
                $request->from_currency,
                $request->to_currency,
                $request->send_amount,
                $request->get_amount
            );
        } catch (Exception $e) {
           $this->helper->one_time_message('error', $e->getMessage());
           return redirect()->route('user_dashboard.crypto_buy_sell.create');
        }


        date_default_timezone_set('UTC');
        $data['expire_seconds'] = $expireSec = 600;
        $expireTime = strtotime('+'.$expireSec.' seconds');
        $data['expire_time'] = $expireTime * 1000;


        setPaymentData($data);

        return redirect()->route('user_dashboard.crypto_buy_sell.payment_confirm');
    }

    public function paymentConfirm()
    {
        try {
            $data = getPaymentData();

            expireTimeCheck($data['expire_time']);
            return view('cryptoexchange::user.crypto-exchange.confirm', $data);
        } catch (Exception $e) {
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect()->route('user_dashboard.crypto_buy_sell.create');
        }
    }


    public function paymentViaGateway(Request $request)
    {

        try {

            $data = getPaymentData();

            expireTimeCheck($data['expire_time']);

            $response = $this->service->gatewayPayment($request, $data);

            if (isset($response['url'])) {
                return redirect($response['url']);
            }

        } catch (Exception $e) {
            $this->helper->one_time_message('error', $e->getMessage());

            return redirect()->route('user_dashboard.crypto_buy_sell.create');
        }


    }

    public function gatewayPaymentComplete()
    {
        try {

            $data = getPaymentParam(request()->params);

            isGatewayValidMethod($data['payment_method']);

            $sessionValue = $data['sessionValue'];

            $processArray = [
                'from_currency' => $sessionValue['from_currency'],
                'to_currency' => $sessionValue['to_currency'],
                'send_amount' => $sessionValue['send_amount'],
                'attachment' => request()->attachment,
                'send_via' => $data['gateway'],
                'payment_details' => '',
                'receive_via' => $sessionValue['receive_via'],
                'receiving_address' => isset($sessionValue['receiving_address']) ? $sessionValue['receiving_address'] : '',
                'payment_method' => $data['payment_method'],
                'bank' => request()->bank,
                'user_id' => $data['user_id'],
                'uuid' => $data['uuid']
            ];

            $paymentData = $this->service->processExchange($processArray);

            $sessionValue['id'] = $paymentData['cryptoExchangeId'];
            $sessionValue['trackUrl'] = url('crypto-exchange/track-transaction', $paymentData['cryptoExchange']->uuid);

            if (isset(request()->execute) && (request()->execute == 'api')) {
                return $paymentData['cryptoExchangeId'];
            }
            clearActionSession();
            setPaymentData($sessionValue);
            return redirect()->route('guest.crypto_exchange.view');
        } catch (Exception $e) {
            if (isset(request()->execute) && (request()->execute == 'api')) {
                return [
                    'status' => 401,
                ];
            }
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect()->route('user_dashboard.crypto_buy_sell.create');
        }
    }

    public function exchangeOfCurrencyConfirm(CryptoUserRequest $request)
    {
        try {
            $data = getPaymentData();

            expireTimeCheck($data['expire_time']);

            $fileName = $this->service->uploadProofFile($request);

            $processArray = [
                'from_currency' => $data['from_currency'],
                'to_currency' => $data['to_currency'],
                'send_amount' => $data['send_amount'],
                'attachment' => request()->attachment,
                'send_via' => $request->pay_with,
                'payment_details' => $request->payment_details,
                'receive_via' => $request->receive_with,
                'receiving_address' => $request->crypto_address,
                'payment_method' => (isset($request->pay_with) && $request->pay_with == 'wallet') ? Mts : '',
                'bank' => null,
                'attach' => $fileName,
                'user_id' => auth()->id(),
                'uuid' => unique_code()
            ];

            $paymentData = $this->service->processExchange($processArray);


            $data = [];
            $data['result'] = $paymentData['cryptoExchange'];
            $data['transInfo']['getAmount']   = $paymentData['cryptoExchange']->get_amount;
            $data['transInfo']['trackUrl']    = url('crypto-exchange/track-transaction', $paymentData['cryptoExchange']->uuid);

            clearActionSession();
            setPaymentData($data);
            return redirect()->route('user_dashboard.crypto_buy_sell.success_page');

        } catch (Exception $e) {

            $this->helper->one_time_message('error', $e->getMessage());
            return redirect()->route('user_dashboard.crypto_buy_sell.create');
        }

    }

    public function cryptoExchangeSuccess()
    {
        try {
            $data =  getPaymentData('forget');
            return view('cryptoexchange::user.crypto-exchange.success', $data);
        } catch (Exception $e) {
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect()->route('user_dashboard.crypto_buy_sell.create');
        }

    }


    public function exchangeOfPrintPdf($trans_id)
    {
        $data = [];
        $data['currencyExchange'] = CryptoExchange::with([
            'fromCurrency:id,code,symbol',
            'toCurrency:id,code,symbol',
        ])->where(['id' => $trans_id])->first();
        generatePDF('cryptoexchange::user.crypto-exchange.exchangeOfPaymentPdf', 'crypto_exchanges_tramsactopm_', $data);
    }

}
