<?php

namespace Modules\CryptoExchange\Entities;

use App\Http\Controllers\Users\EmailController;
use Illuminate\Database\Eloquent\Model;
use App\Http\Helpers\Common;
use App\Models\{Currency,
    Transaction,
    Wallet,
    User
};
use DB, Exception;

class CryptoExchange extends Model
{
    protected $table    = 'crypto_exchanges';

    protected $fillable = [
        'user_id',
        'from_currency',
        'to_currency',
        'uuid',
        'exchange_rate',
        'amount',
        'get_amount',
        'type',
        'receiver_address',
        'file_name',
        'payment_details',
        'receiving_details',
        'verification_via',
        'email_phone',
        'send_via',
        'receive_via',
        'status',
    ];


    protected $helper;
    protected $email;
    public function __construct()
    {
        $this->helper = new Common();
        $this->email  = new EmailController();
    }

    public function fromCurrency()
    {
        return $this->belongsTo(Currency::class, 'from_currency');
    }

    public function toCurrency()
    {
        return $this->belongsTo(Currency::class, 'to_currency');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'transaction_reference_id', 'id')->whereIn('transaction_type_id', [Crypto_Buy, Crypto_Sell, Crypto_Swap]);
    }

    /**
     * [get users firstname and lastname for filtering]
     * @param  [integer] $user      [id]
     * @return [string]  [firstname and lastname]
     */
    public function getExchangesUserName($user)
    {
        return $this->leftJoin('users', 'users.id', '=', 'crypto_exchanges.user_id')
            ->where(['crypto_exchanges.user_id' => $user])
            ->select('users.first_name', 'users.last_name', 'users.id')
            ->first();
    }

    /**
     * [ajax response for search results]
     * @param  [string] $search   [query string]
     * @return [string] [distinct firstname and lastname]
     */
    public function getExchangesUsersResponse($search)
    {
        return $this->leftJoin('users', 'users.id', '=', 'crypto_exchanges.user_id')
            ->where('users.first_name', 'LIKE', '%' . $search . '%')
            ->orWhere('users.last_name', 'LIKE', '%' . $search . '%')
            ->distinct('users.first_name')
            ->select('users.first_name', 'users.last_name', 'crypto_exchanges.user_id')
            ->get();
    }

    public function getExchangesList($from, $to, $status, $currency, $user)
    {
        $conditions = [];

        if (!empty($status) && $status != 'all') {
            $conditions['status'] = $status;
        }

        if (!empty($currency) && $currency != 'all') {
            $conditions['from_currency'] = $currency;
        }
        if (!empty($user)) {
            $conditions['user_id'] = $user;
        }

        $cryptoExchanges = $this->with([
            'user:id,first_name,last_name',
            'fromCurrency:id,code,symbol',
            'toCurrency:id,code,symbol',
        ])->where($conditions);

        if (!empty($from) && !empty($to)) {
            $cryptoExchanges->whereBetween('crypto_exchanges.created_at', [$from, $to]);
        }

        $cryptoExchanges->select('crypto_exchanges.id', 'crypto_exchanges.uuid', 'crypto_exchanges.user_id', 'crypto_exchanges.from_currency', 'crypto_exchanges.to_currency', 'crypto_exchanges.type', 'crypto_exchanges.exchange_rate', 'crypto_exchanges.amount', 'get_amount', 'fee', 'email_phone', 'crypto_exchanges.status', 'crypto_exchanges.created_at');

        return $cryptoExchanges;
    }


    public function getExchangesListForCsvExport($from, $to, $status, $currency, $user)
    {

        $conditions = [];

        if (!empty($from) && !empty($to)) {
            $date_range = 'Available';
        } else {
            $date_range = null;
        }

        if (!empty($status) && $status != 'all') {
            $conditions['crypto_exchanges.status'] = $status;
        }

        if (!empty($user) && $user != 'all') {
            $conditions['crypto_exchanges.user_id'] = $user;
        }

        $field = ['crypto_exchanges.id','crypto_exchanges.created_at', 'crypto_exchanges.user_id', 'crypto_exchanges.from_currency', 'crypto_exchanges.to_currency', 'crypto_exchanges.status', 'crypto_exchanges.fee','crypto_exchanges.amount', 'crypto_exchanges.exchange_rate', 'crypto_exchanges.email_phone'];

        $currencyExchanges = $this->with([
            'fromCurrency:id,code,symbol',
            'toCurrency:id,code,symbol',
            'user:id,first_name,last_name',
        ])->where($conditions);

        // Currency
        if (! empty($currency) && $currency != 'all') {
            $currencyExchanges->whereHas('fromCurrency', function($q) use ($currency) {
                                    $q->where('from_currency', $currency);
                                })
                                ->orWhereHas('toCurrency', function($query) use ($currency) {
                                    $query->where('to_currency', $currency);
                                });
        }

        if (! empty($date_range)) {
            $currencyExchanges->whereDate('crypto_exchanges.created_at', '>=', $from)->whereDate('crypto_exchanges.created_at', '<=', $to)->select($field);
        } else {
            $currencyExchanges->select($field);
        }

        return $currencyExchanges;
    }

    public function createCryptoExchange($arr)
    {
        date_default_timezone_set(config('app.timezone'));
        $cryptoExchange                    = new self;
        $cryptoExchange->user_id           = $arr['user_id'];
        $cryptoExchange->from_currency     = $arr['fromWalletCurrencyId'];
        $cryptoExchange->to_currency       = $arr['toWalletCurrencyId'];
        $cryptoExchange->uuid              = $arr['uuid'];
        $cryptoExchange->exchange_rate     = $arr['destinationCurrencyExRate'];
        $cryptoExchange->amount            = $arr['amount'];
        $cryptoExchange->get_amount        = $arr['getAmount'];
        $cryptoExchange->fee               = $arr['fee'];
        $cryptoExchange->type              = $arr['exchange_type'];
        $cryptoExchange->receiver_address  = $arr['receiver_address'];
        $cryptoExchange->file_name         = $arr['file_name'];
        $cryptoExchange->payment_details   = $arr['payment_details'];
        $cryptoExchange->receiving_details = $arr['receiving_details'];
        $cryptoExchange->verification_via  = $arr['verification_via'];
        $cryptoExchange->email_phone       = $arr['phone'];
        $cryptoExchange->send_via          = ($arr['cryptoPayWith'] == 'mts') ? 'wallet' : $arr['cryptoPayWith'];
        $cryptoExchange->receive_via       = $arr['cryptoRecieve'];
        $cryptoExchange->status            = $arr['status'];
        $cryptoExchange->save();
        return $cryptoExchange;
    }

    public function createExchangeFromTransaction($arr)
    {

        if (isset($arr['payment_method_id'])) {
            $paymentMethod = [Stripe, Paypal, Mts];
            if (in_array($arr['payment_method_id'], $paymentMethod)) {
                $arr['status'] = 'Success';
            }
        }


        date_default_timezone_set(config('app.timezone'));
        $transaction                           = new Transaction();
        $transaction->user_id                  = $arr['user_id'];
        $transaction->currency_id              = $arr['fromWalletCurrencyId'];
        $transaction->uuid                     = $arr['uuid'];
        $transaction->phone                    = $arr['phone'];
        $transaction->transaction_type_id      = $arr['transaction_type_id'] ;
        $transaction->transaction_reference_id = $arr['transaction_reference_id'];
        $transaction->subtotal                 = $arr['amount'];
        $transaction->payment_method_id        = $arr['payment_method_id'] ? $arr['payment_method_id']: NULL;
        $transaction->percentage               = $arr['percentage'] ? $arr['percentage'] : 0;
        $transaction->bank_id                  = $arr['bank_id'] ?? null;
        $transaction->file_id                  = $arr['file_id'] ?? null;
        $transaction->charge_percentage        = $arr['charge_percentage'] ? ($arr['charge_percentage']) : 0;
        $transaction->charge_fixed             = $arr['charge_fixed'] ? $arr['charge_fixed'] : 0;
        $transaction->total                    = '-' . ($arr['finalAmount']);
        $transaction->payment_status           = $arr['status'];
        $transaction->status                   = $arr['status'];
        $transaction->save();

        return $transaction;
    }

    public function createExchangeToTransaction($arr)
    {
        date_default_timezone_set(config('app.timezone'));
        $transaction                           = new Transaction();
        $transaction->user_id                  = $arr['user_id'];
        $transaction->currency_id              = $arr['toWalletCurrencyId'];
        $transaction->uuid                     = $arr['uuid'];
        $transaction->phone                    = $arr['phone'];
        $transaction->transaction_type_id      = $arr['transaction_type_id'];
        $transaction->transaction_reference_id = $arr['transaction_reference_id'];
        $transaction->subtotal                 = $arr['getAmount'];
        $transaction->total                    = $arr['getAmount'];
        $transaction->payment_status           = $arr['status'];
        $transaction->status                   = $arr['status'];
        $transaction->save();
    }

    public function updateFromWallet($arr)
    {
        $arr['fromWallet']->balance = ($arr['fromWallet']->balance - ($arr['finalAmount']));
        $arr['fromWallet']->save();
    }

    public function updateToWallet($arr)
    {
        if (empty($arr['toWallet'])) {
            //Create To Wallet
            $toWallet              = new Wallet();
            $toWallet->user_id     = $arr['user_id'];
            $toWallet->currency_id = $arr['toWalletCurrencyId'];
            $toWallet->is_default  = 'No';
            $toWallet->balance     = $arr['getAmount'];
            $toWallet->save();
        } else {
            $arr['toWallet']->balance = ($arr['toWallet']->balance + ($arr['getAmount']));
            $arr['toWallet']->save();
        }

    }

    public function processExchangeMoneyConfirmation($arr, $clearSessionFrom)
    {
        try {

            DB::beginTransaction();

            if ($arr['payment_method_id'] == Mts) {
                self::updateFromWallet($arr);
            }

            if ($arr['cryptoRecieve'] == 'wallet' && $arr['status'] == 'Success') {
                self::updateToWallet($arr);
            }

            $cryptoExchange                     = self::createCryptoExchange($arr);
            $response['cryptoExchange']         = $cryptoExchange;
            $response['cryptoExchangeId']       = $cryptoExchange->id;
            $arr['transaction_reference_id']    = $cryptoExchange->id;

            $response['transaction']  = self::createExchangeFromTransaction($arr);
            self::createExchangeToTransaction($arr);

            DB::commit();

            $response['status'] = 200;
            return $response;

        } catch (Exception $e) {
            DB::rollBack();
            if ($clearSessionFrom == 'web') {
                $this->helper->clearSessionWithRedirect('transInfo', $e, 'exchange');
            }
            $response = [];
            $response['status'] = 400;
            $response['exchangeCurrencyId'] = null;
            $response['ex']['message'] = $e->getMessage();
            return $response;
        }
    }

}
