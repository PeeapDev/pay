<?php

namespace Modules\CryptoExchange\Services;

use App\Http\Helpers\Common;
use App\Models\CurrencyPaymentMethod;
use App\Models\PaymentMethod;
use Exception;
use Illuminate\Support\Facades\Auth;
use Modules\CryptoExchange\Entities\CryptoExchange;
use Modules\CryptoExchange\Entities\ExchangeDirection;
use Modules\CryptoExchange\Http\Resources\FromCurrenciesCollection;

class CryptoExchangeService
{
    protected $direction;
    protected $fromCurrency;
    protected $toCurrency;
    protected $exchangeType;
    protected $exchangeFrom;
    protected $exchangeRate;
    protected $minAmount;
    protected $maxAmount;
    protected $feesPercentage;
    protected $feesFixed;
    protected $fromCurrencyCode;
    protected $toCurrencyCode;
    protected $sendAmount;
    protected $getAmount;
    protected $userId;
    protected $fromCurrencyLogo;
    protected $toCurrencyLogo;
    protected $gateways;


    public function __construct()
    {
        $this->direction = new ExchangeDirection();
    }

    /**
     * Method getCryptoExchangeDirection
     *
     * Get Directions according to the Exchange type
     *
     * @param $exchangeType
     *
     * @return array
     */
    public function getCryptoExchangeDirection($exchangeType, $fromCurrency=null, $toCurrency = null) : array
    {
        if ($exchangeType == 'crypto_buy' && (preference('available') == 'guest_user' || !Auth::check())){
            $directions = $this->cryptoBuyWithGateway();
        } else {
            $directions = $this->direction->exchangeDirection($exchangeType, 'fromCurrency:id,name,symbol,code,type,logo,status');
        }

        if (!count($directions)) {
            throw new Exception(__('Direction not available.'));
        }

        if (!is_null($fromCurrency) && !is_null($toCurrency)) {
            $firstDirection = $directions->where('from_currency_id', $fromCurrency)->where('to_currency_id', $toCurrency)->first();
        } else {
            $firstDirection = $directions->first();
        }

        $fromCurrency = (!is_null($fromCurrency) )? $fromCurrency : $firstDirection->from_currency_id;

        $toCurrencies = $this->getToCurrencies($fromCurrency, $exchangeType);

        $collection = collect($toCurrencies);
        $firstElement = $collection->first();

        $direction = $directions->where('from_currency_id', $fromCurrency)->where('to_currency_id', $firstElement->id)->first();

        return [
            'direction' => $direction,
            'min_amount' => $direction->min_amount,
            'fromCurrencies' => new FromCurrenciesCollection($directions->unique('from_currency_id')),
            'toCurrencies' => $toCurrencies,
        ];
    }



    /**
     * get Direction which has been set with payment gateway
     *
     * @return void
     */
    public function cryptoBuyWithGateway()
    {
        $cryptoBuyDirections =  $this->direction->exchangeDirectionWithGateway('crypto_buy', 'gateways', 'fromCurrency');

        $fromCurrencies['id'] = [];
        foreach ($cryptoBuyDirections as $cryptoBuyDirection)
        {
            $gateways = explode(',' , $cryptoBuyDirection->gateways);

            $currencyPaymentMethods = [];
            foreach($gateways as $gateway)
            {
                $currencyPaymentMethods = CurrencyPaymentMethod::with('method')
                                            ->where('method_id', $gateway)
                                            ->where('currency_id', $cryptoBuyDirection->from_currency_id)
                                            ->where('activated_for', 'like', "%crypto_buy%")
                                            ->get();

                if (count($currencyPaymentMethods)) {
                    $fromCurrencies['id'][] =  $cryptoBuyDirection->id;
                }
            }
        }

        $fromCurrencies = $cryptoBuyDirections->whereIn('id', $fromCurrencies['id']);

        return $fromCurrencies;
    }



    /**
     * Method getToCurrencies
     *
     * Get all the currencies according
     *
     * @param $from_currency
     * @param $exchangeType
     *
     * @return void
     */
    public function getToCurrencies($from_currency, $exchangeType)
    {
        $toCurrencies = $this->direction->getCurrencies($from_currency, $exchangeType);

        if (is_null($toCurrencies)) {
            throw new Exception(__('Direction not available.'));
        }

        return $toCurrencies;
    }

    public function getDirectionAmount($from_currency, $to_currency, $amount, $get_amount)
    {
        $this->sendAmount = $amount;
        $this->getAmount = $get_amount;

        $this->getTransactionDirection($from_currency, $to_currency);

        $this->setAmount();

        return [
            'send_amount' => ($this->exchangeType == 'crypto_buy') ? decimalFormat($this->sendAmount) : cryptoFormat($this->sendAmount),
            'get_amount' => ($this->exchangeType == 'crypto_sell') ? decimalFormat($this->getAmount ) : cryptoFormat($this->getAmount),
            'exchange_rate' => $this->exchangeRate,
            'exchange_fee' => $this->exchangeFee(),
            'exchange_rate_display' => $this->exchangeRateDisplay(),
            'message' => $this->checkLimit('message'),
        ];

    }


    public function confirmCryptoExchange($from_currency, $to_currency, $amount)
    {
        $this->getTransactionDirection($from_currency, $to_currency);

        $this->sendAmount = $amount;

        $this->userId = auth()->id() ?? null;

        $this->setAmount();

        $this->checkLimit();

        return [
            'exchange_type' => $this->exchangeType,
            'from_currency_code' => $this->fromCurrencyCode,
            'to_currency_code' => $this->toCurrencyCode,
            'from_currency_logo' => $this->fromCurrencyLogo,
            'to_currency_logo' => $this->toCurrencyLogo,
            'from_currency' => $this->fromCurrency,
            'to_currency' => $this->toCurrency,
            'send_amount' => ($this->exchangeType == 'crypto_buy') ? decimalFormat($this->sendAmount) : cryptoFormat($this->sendAmount),
            'get_amount' => ($this->exchangeType == 'crypto_sell') ? decimalFormat($this->getAmount ) : cryptoFormat($this->getAmount),
            'exchange_rate' => formatNumber($this->exchangeRate, $to_currency),
            'exchange_fee' => $this->exchangeFee(),
            'exchange_rate_display' => $this->exchangeRateDisplay(),
            'message' => $this->checkLimit(),
            'merchantAddress' =>  $this->getMerchantAddress(),
            'sendingOption'  => $this->sendingOption(),
            'receivingOption' => $this->receivingOption(),
            'pref' => preference('verification'),
            'fromWallet' => $this->getWallet(),
            'currencyPaymentMethods' => $this->getPaymentMethod(),
        ];

    }


    /**
     * Method processExchange
     *
     * File upload & response remain
     *
     * @param $data $data [explicite description]
     *
     * @return void
     */
    public function processExchange($data)
    {
        try {
            $this->getTransactionDirection($data['from_currency'], $data['to_currency']);

            $this->sendAmount = $data['send_amount'];

            $this->setAmount();

            $this->checkLimit('submit');

            $this->userId = $data['user_id'];

            if (isset($data['payment_method']) && $data['payment_method'] == Mts) {

                $data['wallet'] = $this->walletCheck();
            }

            $data['toWallet'] =  (new Common())->getUserWallet([],
                ['user_id' => $this->userId, 'currency_id' => $this->toCurrency],
                ['id', 'balance']
            );

            $data['status'] = 'Pending';

            if (isset($data['payment_method'])) {
                $paymentMethod = [Stripe, Paypal, Mts];
                if (in_array($data['payment_method'], $paymentMethod)) {
                    if ( $data['receive_via'] == 'wallet') {
                        $data['status'] = 'Success';
                    }
                }
            }

            $response = (new CryptoExchange())->processExchangeMoneyConfirmation($this->setExchangeArray($data), 'web');


            if ($response['status'] != 200) {
                if (empty($response['cryptoExchangeId'])) {
                    throw new Exception(__($response['ex']['message']));
                }
            }

            (new \Modules\CryptoExchange\Services\Mail\NotifyAdminOnCryptoMailService)->send($response['cryptoExchange'], ['type' => 'crypto-exchange', 'medium' => 'email']);

            return $response;

        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

    }


    public function setAmount()
    {
        if (is_null($this->sendAmount)) {
            $this->getAmount =  floatval($this->getAmount);
            $this->sendAmount = $this->getAmount / $this->exchangeRate ;
        }

        if (is_null($this->getAmount)) {
            $this->sendAmount = floatval($this->sendAmount);
            $this->getAmount = $this->sendAmount * $this->exchangeRate;
        }
    }


    public function getTransactionDirection($from_currency, $to_currency)
    {
        $direction = $this->direction->getDirection($from_currency, $to_currency);

        if (empty($direction)) {
            throw new Exception(__('Direction not available.'));
        }

        if ($direction->status  == 'Inactive') {
           throw new Exception(__('Exchange direction not active, please try again.'));
        }

        $this->fromCurrency = $direction->from_currency_id;
        $this->toCurrency = $direction->to_currency_id;
        $this->exchangeType = $direction->type;
        $this->exchangeFrom = $direction->exchange_from;
        $this->minAmount = $direction->min_amount;
        $this->maxAmount = $direction->max_amount;
        $this->feesPercentage = $direction->fees_percentage;
        $this->feesFixed = $direction->fees_fixed;
        $this->fromCurrencyCode = optional($direction->fromCurrency)->code;
        $this->toCurrencyCode = optional($direction->toCurrency)->code;
        $this->fromCurrencyLogo = optional($direction->fromCurrency)->logo;
        $this->toCurrencyLogo = optional($direction->toCurrency)->logo;
        $this->exchangeRate = ($this->exchangeFrom == 'api') ? getCryptoCurrencyRate($this->fromCurrencyCode, $this->toCurrencyCode) :  $direction->exchange_rate;
        $this->gateways = $direction->gateways;

    }

    public function getPaymentMethod()
    {
        if ($this->exchangeType !== 'crypto_buy') {
            return null;
        }
        $gateway_list = explode(',', $this->gateways);
        $paymentMethods = ExchangeDirection::currencyPaymentMethodList($this->fromCurrency , $gateway_list);
        if ($this->getWallet()) {
            $paymentMethod = PaymentMethod::where(['id' => Mts])->get(['id', 'name']);
            $paymentMethods = $paymentMethod->merge($paymentMethods);
        }
        return $paymentMethods;

    }

    public function walletCheck()
    {
        $wallet = $this->getWallet();

        if (is_null($wallet)) {
            throw new Exception(__('Wallet not Available'));
        } elseif ($wallet->balance < $this->sendAmount) {
            throw new Exception(__('Balance is not Available'));
        }

        return $wallet;
    }

    public function getWallet()
    {
        return (new Common())->getUserWallet(
            ['currency:id,code,symbol'],
            ['user_id' => $this->userId, 'currency_id' => $this->fromCurrency],
            ['id', 'currency_id', 'balance']
        );
    }

    public function exchangeFee()
    {
        return formatNumber( $this->getPercentage() + $this->feesFixed, $this->fromCurrency);

    }

    public function getPercentage()
    {
       return  $this->sendAmount * ($this->feesPercentage / 100);
    }

    public function checkLimit($type = 'submit')
    {
        $message = '';

        if ($this->sendAmount < $this->minAmount || $this->sendAmount > $this->maxAmount) {

            if ($this->exchangeType == 'crypto_buy') {
                $message = __('Min Amount :x , Max Amount :y', ['x' => decimalFormat($this->minAmount), 'y' => decimalFormat($this->maxAmount)]);
            } else {
                $message = __('Min Amount :x , Max Amount :y', ['x' => cryptoFormat($this->minAmount), 'y' => cryptoFormat($this->maxAmount)]);
            }

            if ($type == 'submit') {
                throw new Exception($message);
            }

        }
        return $message;
    }

    public function getMerchantAddress()
    {
        $currency =  (new Common)->getCurrencyObject(['id' => $this->fromCurrency], ['address']);

        if ($currency) {
            return $currency->address;
        }

        return null;
    }

    public function receivingOption()
    {
        if ($this->exchangeType == 'crypto_sell') {
            return ['wallet'];
        }

        return ['wallet', 'address'];

    }

    public function sendingOption()
    {
        if ($this->exchangeType == 'crypto_buy') {
            return ['gateway'];
        }

        if (!is_null($this->getWallet())) {
            return ['wallet', 'address'];
        };

        return ['address'];

    }

    public function exchangeRateDisplay()
    {
        if ($this->exchangeType == 'crypto_buy') {
            return '1 ' . $this->fromCurrencyCode . ' = ' . cryptoFormat($this->exchangeRate, $this->toCurrency) . ' '. $this->toCurrencyCode;
        } else {
            return '1 ' . $this->fromCurrencyCode . ' = ' . decimalFormat($this->exchangeRate, $this->toCurrency) . ' '. $this->toCurrencyCode;
        }

    }

    public function validPairCheck($from, $to, $type)
    {
        if (!currencyPairCheck($from, $to, $type)) {
            throw new Exception(__('Invalid currency pair.'));
        }
    }

    public function uploadProofFile($request)
    {
        $fileName = '';
        if (isset($request->proof_file)) {
            $fileName = insertDetailsFile($request->proof_file, public_path('uploads/files/crypto-details-file'));
            if (!$fileName) {
                throw new Exception(__('Invalid file type.'));
            }
        }
        return $fileName;
    }

    public function setExchangeArray($data)
    {
       return [
            'unauthorisedStatus'        => null,
            'user_id'                   => $this->userId,
            'toWalletCurrencyId'        => $this->toCurrency,
            'fromWalletCurrencyId'      => $this->fromCurrency,
            'fromWallet'                => $data['wallet'] ?? null,
            'toWallet'                  => $data['toWallet'] ?? null ,
            'payment_method_id'         => (isset($data['payment_method'])) ? $data['payment_method'] : null ,
            'uuid'                      => $data['uuid'],
            'destinationCurrencyExRate' => $this->exchangeRate,
            'amount'                    => $this->sendAmount,
            'fee'                       => $this->exchangeFee(),
            'finalAmount'               => $this->exchangeFee() + $this->sendAmount,
            'getAmount'                 => $this->getAmount,
            'transaction_type_id'       => $this->getTransactionType(),
            'percentage'                => $this->feesPercentage,
            'charge_percentage'         => $this->getPercentage(),
            'charge_fixed'              => $this->feesFixed,
            'exchange_type'             => $this->exchangeType,
            'fromCurrCode'              => $this->fromCurrencyCode,
            'toCurrCode'                => $this->toCurrencyCode,
            'merchantAddress'           => $this->getMerchantAddress(),
            'receiver_address'          => ($data['receive_via'] == 'address') ? $data['receiving_address'] : '',
            'file_name'                 => (isset($data['attach'])) ? $data['attach'] : '',
            'payment_details'           => (isset($data['payment_details'])) ? $data['payment_details'] : null,
            'receiving_details'         => (isset($data['receiving_details'])) ? $data['receiving_details'] : null,
            'verification_via'          => '',
            'phone'                     => NULL,
            'bank_id'                   => (isset($data['bank'])) ? $data['bank'] : null,
            'file_id'                   => (isset($data['attachment'])) ? $data['attachment'] : null,
            'cryptoPayWith'             => $data['send_via'],
            'cryptoRecieve'             => $data['receive_via'],
            'phone'                     => (isset($data['email_or_phone'])) ? $data['email_or_phone'] : null ,
            'status'                    => $data['status'],
        ];

    }

    public function getTransactionType()
    {
        return ($this->exchangeType == 'crypto_buy') ? Crypto_Buy : (($this->exchangeType == 'crypto_sell') ? Crypto_Sell : Crypto_Swap);

    }

    public function gatewayPayment($request, $session = null)
    {
        $data = $session ?? $this->confirmCryptoExchange(
            $request->from_currency,
            $request->to_currency,
            $request->send_amount,
            $request->get_amount
        );


        if(isset($request->receive_with)) {
            $data['receive_via'] = $request->receive_with;
        }

        if (isset($request->crypto_address)) {
            $data['receiving_address'] = $request->crypto_address;
        }

        $redirectUrl = isset($data['redirectUrl']) ? $data['redirectUrl'] : route('user_dashboard.crypto_buy_sell.gateway_payment');


        $pm = PaymentMethod::where(['id' => $request->gateway])->first(['id', 'name']);

        $paymentData = [
            'currency_id' =>  $data['from_currency'],
            'currencyCode' => $data['from_currency_code'],
            'total' => $data['send_amount'] + $data['exchange_fee'],
            'amount' => $data['send_amount'] + $data['exchange_fee'],
            'totalAmount' => $data['send_amount'] + $data['exchange_fee'],
            'transaction_type' => Crypto_Buy,
            'payment_type' => 'crypto_buy',
            'payment_method' =>  $pm->id,
            'redirectUrl' => $redirectUrl,
            'success_url' => route('guest.crypto_exchange.view'),
            'cancel_url' => url('payment/fail'),
            'gateway' => strtolower($pm->name),
            'uuid' => unique_code(),
            'sessionValue' => $data,
            'currencyType' => 'fiat',
            'user_id' => auth()->id() ?? null
        ];

        if ($pm->id == Bank) {
            $paymentData['banks'] = getBankList($data['from_currency'], 'deposit');
        }


        return [
            'url' => gatewayPaymentUrl($paymentData)
        ];

    }


}
