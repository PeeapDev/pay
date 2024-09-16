<?php

if (!function_exists('getCryptoCurrencyRate')) {

    function getCryptoCurrencyRate($from, $to)
    {
        $api_key = settings('crypto_compare_api_key');
        $enabledCryptoApi = settings('crypto_compare_enabled_api');
        $apiURL = ($api_key !== '' && $enabledCryptoApi == 'Enabled') ? "https://min-api.cryptocompare.com/data/price?fsym=" . $from ."&tsyms=" . $to ."&api_key=" . $api_key : "https://min-api.cryptocompare.com/data/price?fsym=" . $from ."&tsyms=" . $to ;
        $response = \Illuminate\Support\Facades\Http::get($apiURL);
        if ($response->status() == 200) {
            $exchangeRate = json_decode($response, true);
            if (!isset($exchangeRate[$to])) {
                return false;
            }
            return $exchangeRate[$to];
        }
        return false;
    }
}

if (!function_exists('currencyType')) {

    function currencyType($value)
    {
        return \App\Models\Currency::where('id', $value)->value('type');

    }
}


if (!function_exists('cryptoValidity')) {

    function cryptoValidity($value = 'all')
    {
        return (preference('available') == 'all' || preference('available') == $value) ? true : false;
    }
}

if (!function_exists('transactionTypeCheck')) {

    function transactionTypeCheck($value = 'all')
    {
        return (preference('transaction_type') == 'all' || preference('transaction_type') == $value) ? true : false;
    }
}

if (!function_exists('insertDetailsFile')) {

    function insertDetailsFile($file, $uploadPath)
    {
        if (!empty($file)) {
            $request = app(\Illuminate\Http\Request::class);

            if ($request->hasFile('proof_file')) {
                $fileName     = $request->file('proof_file');
                $originalName = $fileName->getClientOriginalName();
                $uniqueName   = strtolower(time() . '.' . $fileName->getClientOriginalExtension());
                $extension    = strtolower($fileName->getClientOriginalExtension());

                if (in_array($extension, getFileExtensions(2))) {
                    $fileName->move($uploadPath, $uniqueName);
                    return $uniqueName;
                } else {
                    Session::forget('transInfo');
                    return false;
                }
            }
        }
    }
}

if (!function_exists('currencyPairCheck')) {

    function currencyPairCheck($fromCurrencyId, $toCurrencyId, $cryptoExchangetype)
    {
        $fromCurrencyType = currencyType($fromCurrencyId);
        $toCurrencyType = currencyType($toCurrencyId);

        switch ($cryptoExchangetype) {
            case 'crypto_swap':
                return ($fromCurrencyType == 'fiat' || $toCurrencyType == 'fiat') ? false : true;
                break;

            case 'crypto_buy':
                return ($fromCurrencyType == 'crypto' || $toCurrencyType == 'fiat') ? false : true;
                break;

            case 'crypto_sell':
                return ($fromCurrencyType == 'fiat' || $toCurrencyType == 'crypto') ? false : true;
                break;

            default:
                return false;
                break;
        }
    }
}

if (!function_exists('expireTimeCheck')) {

    function expireTimeCheck($expireTime)
    {
        date_default_timezone_set('UTC');
        $currentTime = time();
        if ($expireTime < $currentTime) {
            throw new Exception(__('Transaction time over.'));
        }
    }
}

if (!function_exists('qrGenerate')) {

    function qrGenerate($qrInfo)
    {
        return "https://api.qrserver.com/v1/create-qr-code/?data=".$qrInfo;
    }
}


if (!function_exists('currencyLogo')) {

    function currencyLogo($logo)
    {
        if ( file_exists(public_path('uploads/currency_logos/' . $logo)) ) {
            return url('public/uploads/currency_logos/' . $logo);
        }
        return false;
    }
}

if (!function_exists('cryptoExchangeTransactionUpdate')) {

    function cryptoExchangeTransactionUpdate($transaction, $status)
    {
        $transaction->payment_status = $status; 
        $transaction->save();
    }
}







