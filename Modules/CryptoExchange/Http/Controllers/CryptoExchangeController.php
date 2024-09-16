<?php

namespace Modules\CryptoExchange\Http\Controllers;

use App\Http\Controllers\Users\EmailController;
use Modules\CryptoExchange\Http\Requests\{CryptoBuySellRequest,
    ReceivingInfoRequest,
    CryptoBuySellSuccessRequest
};
use Session, Validator, Auth, URL;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Helpers\Common;
use App\Models\{Transaction,
    Bank
};
use Exception;
use Modules\CryptoExchange\Entities\{
    ExchangeDirection,
    PhoneVerification,
    CryptoExchange
};
use Modules\CryptoExchange\Services\CryptoExchangeService;

class CryptoExchangeController extends Controller
{
    protected $helper;
    protected $email;
    protected $service;

    public function __construct()
    {
        $this->helper = new Common();
        $this->email = new EmailController();
        $this->service = new CryptoExchangeService();
    }

    // Initiate Exchange (first Page)
    public function cryptoExchange()
    {
        $this->sessionForget();

        setActionSession();

        if (Auth::check() && cryptoValidity('auth') ) {
            return redirect()->route('user_dashboard.crypto_buy_sell.create');
        }

        if (!m_g_c_v('Q1JZUFRPRVhDSEFOR0VfU0VDUkVU') && m_aic_c_v('Q1JZUFRPRVhDSEFOR0VfU0VDUkVU')) {
            return view('vendor.installer.errors.user');
        }


        $pref = preference('transaction_type');
        $type = ($pref == 'crypto_buy_sell') ? 'crypto_buy' : 'crypto_swap';


        if (route('guest.crypto_exchange.verification') == url()->previous()) {
            $paymentData = getPaymentData();
            $type = $paymentData['exchange_type'];
        }

        $data = [
            'menu' => 'Crypto Exchange',
            'pref' => $pref,
            'exchange_type' => $type,
            'min_amount' => 0,
            'fromCurrencies' => [],
            'toCurrencies' => [],
            'direction' => null
        ];

        try {
            $fromCurrency = isset($paymentData) ? $paymentData['from_currency'] : null;
            $toCurrency = isset($paymentData) ? $paymentData['to_currency'] : null;
            $data = array_merge($data,  $this->service->getCryptoExchangeDirection($type, $fromCurrency, $toCurrency));
            $data['min_amount'] = isset($paymentData) ? $paymentData['send_amount'] : $data['min_amount'];
        } catch (Exception $e) {
            $this->helper->one_time_message('error', $e->getMessage());
        }

        return view('cryptoexchange::frontend.crypto.exchange', $data);
    }

    // Set initial session value (verification page)
    public function cryptoBuySell(CryptoBuySellRequest $request)
    {
        try {
            $this->service->validPairCheck(
                $request->from_currency,
                $request->to_currency,
                $request->from_type
            );

            $data = $this->service->confirmCryptoExchange(
                $request->from_currency,
                $request->to_currency,
                $request->send_amount
            );

            setPaymentData($data);

        } catch (Exception $e) {
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect()->route('guest.crypto_exchange.home');
        }

        return view('cryptoexchange::frontend.crypto.verification', $data);
    }

    // provide receiving option
    public function cryptoBuySellReceive()
    {
        try {
            $data = getPaymentData();

            $data['menu'] = 'Crypto Exchange';
            $data['crypto_phone'] = session('transInfo_crypto_phone');

            setPaymentData($data);
            return view('cryptoexchange::frontend.crypto.receiving_details', $data);

        } catch (Exception $e) {
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect()->route('guest.crypto_exchange.home');
        }

    }

    // Set receiving option in session
    public function receivingInfoStore(ReceivingInfoRequest $request)
    {
        try {
            $data = getPaymentData();
            $data['receiving_address'] = $request->crypto_address;
            $data['receiving_details'] = $request->receiving_details;
            setPaymentData($data);
            return redirect()->route('guest.crypto_exchange.gateway');
        } catch (Exception $e) {
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect()->route('guest.crypto_exchange.home');
        }

    }

    // Redirect to payment page
    public function cryptoBuySellGateway()
    {
        if (URL::previous() != url('crypto-exchange/receiving-info')) {
            return redirect()->route('guest.crypto_exchange.home');
        }

        try {
            $data = getPaymentData();
            $data['url'] = ($data['exchange_type'] == 'crypto_buy') ? url('crypto-exchange/payment') : url('crypto-exchange/success');
            date_default_timezone_set(preference('dflt_timezone'));
            $data['expireTime'] = date("F d, Y h:i:s A", strtotime('+5 minutes'));
            setPaymentData($data);

            return redirect()->route('guest.crypto_exchange.payment-gateway');
        } catch (Exception $e) {
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect()->route('guest.crypto_exchange.home');
        }


    }

    // Provide payment option
    public function cryptoBuySellPaymentGateway()
    {
        try {
            $data =  getPaymentData();
            return view('cryptoexchange::frontend.crypto.gateway', $data);
        } catch (Exception $e) {
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect()->route('guest.crypto_exchange.home');
        }
    }

    // Exchange Complete for Crypto Swap & Crypto Sell
    public function cryptoBuySellSuccess(CryptoBuySellSuccessRequest $request)
    {
        try {
            $data =  getPaymentData();

            $fileName = $this->service->uploadProofFile($request);

            $processArray = [
                'from_currency' => $request->from_currency,
                'to_currency' => $request->to_currency,
                'send_amount' => $request->send_amount,
                'payment_details' =>  isset($request->payment_details) ? $request->payment_details : '',
                'attachment' => $request->attachment,
                'receive_via' => $request->receive_via,
                'email_or_phone' => $data['crypto_phone'],
                'receiving_address' => $data['receiving_address'],
                'receiving_details' => $data['receiving_details'],
                'attach' => $fileName,
                'send_via' => 'address',
                'user_id' => null,
                'payment_method' => null,
                'bank' => null,
                'uuid' => unique_code()
            ];

            $response = $this->service->processExchange($processArray);

            $data['uuid'] = $response['cryptoExchange']->uuid;
            $data['id'] = $response['cryptoExchangeId'];
            $this->sessionForget();
            clearActionSession();

            setPaymentData($data);

            return redirect()->route('guest.crypto_exchange.view');

        } catch (Exception $e) {
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect()->route('guest.crypto_exchange.home');
        }

    }

    // Crypto Buy redirect to payment gateway
    public function cryptoBuySellPayment(CryptoBuySellSuccessRequest $request)
    {
        try {

            $data =  getPaymentData();
            $data['receive_via'] = 'address';
            $data['redirectUrl'] = route('guest.crypto_exchange.payment_success');
            $data['payment_method'] = $request->gateway;

            expireTimeCheck($data['expireTime']);

            $response = $this->service->gatewayPayment($request, $data);

            if (isset($response['url'])) {
                return redirect($response['url']);
            }

        }  catch (Exception $e) {
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect()->route('guest.crypto_exchange.home');
        }


    }

    // Payment complete via payment gateway
    public function cryptoPaymentSuccess()
    {
        try {

            $sessionValue = getPaymentParam(request()->params);

            $data = $sessionValue['sessionValue'];

            isGatewayValidMethod($data['payment_method']);

            expireTimeCheck($data['expireTime']);

            $processArray = [
                'from_currency' => $data['from_currency'],
                'to_currency' => $data['to_currency'],
                'send_amount' => $data['send_amount'],
                'payment_details' =>  isset($data['payment_details']) ? $data['payment_details'] : '',
                'attachment' => request()->attachment,
                'receive_via' => 'address',
                'email_or_phone' => $data['crypto_phone'],
                'receiving_address' => $data['receiving_address'],
                'receiving_details' => $data['receiving_details'],
                'attach' =>'',
                'send_via' => 'gateway',
                'user_id' => null,
                'payment_method' => $data['payment_method'],
                'bank' => request()->bank,
                'uuid' => $sessionValue['uuid']
            ];

            $response = $this->service->processExchange($processArray);

            if (isset(request()->execute) && (request()->execute == 'api')) {
                return $response['cryptoExchangeId'];
            }

            $data['uuid'] = $sessionValue['uuid'];
            $data['id'] = $response['cryptoExchangeId'];

            setPaymentData($data);

            return redirect()->route('guest.crypto_exchange.view');

        } catch (Exception $e) {
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect()->route('guest.crypto_exchange.home');

        }

    }

    // Success page view
    public function successView()
    {
        try {
            $paymentData =  getPaymentData('forget');
            $data = isset($paymentData['sessionValue']) ? $paymentData['sessionValue'] : $paymentData ;
            $data['user_id'] = isset($paymentData['user_id']) ? $paymentData['user_id'] : null ;
            $data['trackUrl'] = isset($data['trackUrl']) ? $data['trackUrl'] : url('crypto-exchange/track-transaction', $paymentData['uuid']);
            $data['id'] = isset($paymentData['transaction_id']) ? $paymentData['transaction_id'] : $paymentData['id'];
            return view('cryptoexchange::frontend.crypto.success', $data);
        } catch (Exception $e) {
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect()->route('guest.crypto_exchange.home');
        }

    }

    // Transaction status check via unique id of transaction
    public function trackTransaction($uuid)
    {
        $data = ['menu' => 'Crypto Exchange'] ;
        $data['transInfo'] = CryptoExchange::with('fromCurrency:id,logo,code','toCurrency:id,logo,code')->where('uuid', $uuid)->firstOrFail();
        return view('cryptoexchange::frontend.crypto.transaction_track', $data);
    }

    // Download Exchange Pdf
    public function exchangeOfPrintPdf($trans_id)
    {
        $data = [];
        $data['transactionDetails'] = $transaction = Transaction::with(['cryptoapi_log:id,object_id,payload', 'currency:id,symbol,code'])
            ->where(['uuid' => $trans_id])
            ->first(['id','uuid', 'created_at', 'status', 'currency_id', 'payment_method_id', 'subtotal', 'charge_percentage', 'charge_fixed', 'total']);

        $data['payload'] = $payload = json_decode($transaction->cryptoapi_log->payload);

        $data['direction'] =  ExchangeDirection::getDirection($payload->exchangeToCurrencyId, $payload->exchangeFromCurrencyId);

        generatePDF('cryptoexchange::frontend.crypto.exchangeOfPaymentPdf', 'crypto_exchanges_transaction_', $data);

    }

    // Ajax request response
    public function directionCurrencies()
    {
        return response()->json([
            'directionCurrencies' => ExchangeDirection::getCurrencies(request()->from_currency_id,  request()->type),
        ]);
    }

    public function getDirectionAmount()
    {
        $data = $this->service->getDirectionAmount(
            request()->from_currency_id,
            request()->to_currency_id,
            request()->send_amount,
            request()->get_amount
        );

        $data['status'] =  ($data['message'] == '') ? 200 : 401;

        return response()->json([
            'success' => $data,
        ]);
    }

    public function getTabDirection()
    {
        $type = request()->direction_type;

        try {
            $data = $this->service->getCryptoExchangeDirection($type);
            $data['status'] = 200;
        } catch (Exception $e) {
            $data['status'] = '401';
        }

        return $data;
    }

    // Ajax request end


    public function bankList($banks, $currencyPaymentMethod)
    {
        $selectedBanks = [];
        $i = 0;
        foreach ($banks as $bank)
        {
            foreach ($currencyPaymentMethod as $cpm)
            {
                if ($bank->id == json_decode($cpm->method_data)->bank_id) {
                    $selectedBanks[$i]['id'] = $bank->id;
                    $selectedBanks[$i]['bank_name'] = $bank->bank_name;
                    $selectedBanks[$i]['is_default'] = $bank->is_default;
                    $selectedBanks[$i]['account_name'] = $bank->account_name;
                    $selectedBanks[$i]['account_number'] = $bank->account_number;
                    $i++;
                }
            }
        }
        return $selectedBanks;
    }


    public function getBankDetailOnChange(Request $request)
    {
        $bank = Bank::with('file:id,filename')->where(['id' => $request->bank])->first(['bank_name', 'account_name', 'account_number', 'file_id']);
        if ($bank) {
            $data['status'] = true;
            $data['bank']   = $bank;
            if (!empty($bank->file_id)) {
                $data['bank_logo'] = $bank->file->filename;
            }
        } else {
            $data['status'] = false;
            $data['bank'] = __('Bank Not Found');
        }
        return $data;
    }


    public function paymentCancel()
    {
        clearActionSession();
        $this->helper->one_time_message('error', __('You have cancelled your payment.'));
        return back();
    }

    // Verification start
    public function completePhoneVerification(Request $request)
    {
        $phoneFormatted = str_replace('+' . $request->carrierCode, "", $request->phone);
        if ($request->code)  {
            $verificationDetails = PhoneVerification::where(['phone' => $phoneFormatted])->first(['code']);
            if ($request->code == $verificationDetails->code) {

                Session::put('transInfo_crypto_phone', $request->carrierCode . $phoneFormatted);


                return response()->json([
                    'status'  => true,
                    'message' => __('Phone number verified successfully.'),
                    'success' => "alert-success",
                ]);
            } else  {
                return response()->json([
                    'status'  => false,
                    'message' => __('Verification code doesn\'t match.'),
                    'error'   => "alert-danger",
                ]);
            }
        }  else {
            return response()->json([
                'status'  => 500,
                'message' => __('Please enter verification code.'),
                'error'   => "alert-danger",
            ]);
        }
    }

    public function generatedPhoneVerificationCode(Request $request)
    {
        $data = ['status' => false, 'message' => 'No'];
        $sixDigitNumber = otpCode6();
        $phoneFormatted = str_replace('+' . $request->carrierCode, "", $request->phone);
        $verification = PhoneVerification::firstOrCreate([
            'phone' => $phoneFormatted
        ]);
        $verification->code = $sixDigitNumber;
        $verification->save();
        //SMS
        if (!empty($request->phone)) {
            if (!empty($request->carrierCode) && !empty($request->phone)) {
                $message = __(':x is your crypto transaction verification code.', ['x' => $sixDigitNumber]);
                if (checkAppSmsEnvironment() == true ) {
                    if (!empty(getSmsConfigDetails()) && getSmsConfigDetails()->status == 'Active') {
                        $data['status'] = true;
                        $data['message'] = 'Yes';
                        sendSMS('+'.$request->carrierCode . $phoneFormatted, $message);
                    } else  {
                        $data['status'] = false;
                        $data['message'] = 'No';
                    }
                }
            }
        }
        return response()->json(['data' => $data ]);
    }

   
    public function generatedEmailVerificationCode(Request $request)
    {
        $data = ['status' => false, 'message' => 'No'];

        try {
            $sixDigitNumber = otpCode6();
            $email = $request->email;

            // Send email
            if (!empty($request->email)) {
                $message = __(':x is your crypto transaction verification code.', ['x' => $sixDigitNumber]);
                $subject = __('Verification Code');
                if (checkAppMailEnvironment() == true ) {
                    $this->email->sendEmail($email, $subject, $message);
                    $data['status'] = true;
                    $data['message'] = 'Yes';
                }
            }

            $verification = PhoneVerification::firstOrCreate([
                'phone' => $email
            ]);
            $verification->code = $sixDigitNumber;
            $verification->save();
            return response()->json(['data' => $data ]);

        } catch (Exception $th) {
            return response()->json(['data' => $data ]);
        }

    }

    public function completeEmailVerification(Request $request)
    {
        $email = $request->email;
        $validation = Validator::make($request->all(), [
            'code' => 'required',
            'email' => 'required|email',
        ]);
        if ($validation->passes()) {
            $verificationDetails = PhoneVerification::where(['phone' => $email])->first(['code']);
            if ($request->code == $verificationDetails->code) {
                Session::put('transInfo_crypto_phone', $request->email);
                return response()->json([
                    'status'  => true,
                    'message' => __('Phone number verified successfully.'),
                    'success' => "alert-success",
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => __('Verification code doesn\'t match.'),
                    'error' => "alert-danger",
                ]);
            }
        }  else {
            return response()->json([
                'status' => 500,
                'message' => $validation->errors()->all(),
                'error' => "alert-danger",
            ]);
        }
    }
    // Verification End


    // All session forget after complete the transaction
    public function sessionForget()
    {
        session()->forget([
            'transInfo_receiving_details',
            'transInfo_crypto_address',
            'transInfo_crypto_phone',
            'payment_method_id',
            'transInfo',
            'amount',
            'bank',
        ]);
    }


}
