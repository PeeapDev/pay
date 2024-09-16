<?php

namespace Modules\CryptoExchange\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Modules\CryptoExchange\Http\Requests\CryptoExchangeRequest;
use Modules\CryptoExchange\Http\Requests\ProcessRequest;
use Modules\CryptoExchange\Services\CryptoExchangeService;

class CryptoExchangeApiController extends Controller
{
    protected $service;

    public function __construct()
    {
        $this->service = new CryptoExchangeService();

    }

    public function exchangeDirection(Request $request)
    {
        try {

            $type = (preference('transaction_type') == 'crypto_buy_sell') ? 'crypto_buy' : 'crypto_swap';
            $exchange_type = isset($request->exchange_type) ? $request->exchange_type : $type;

            return $this->successResponse(
                $this->service->getCryptoExchangeDirection($exchange_type)
            );
        } catch (Exception $th) {
            return $this->unprocessableResponse([], $th->getMessage());
        }

    }

    public function directionToCurrencies(Request $request)
    {
        try {
            return $this->successResponse(
                $this->service->getToCurrencies($request->from_currency, $request->exchange_type)
            );
        } catch (Exception $th) {
            return $this->unprocessableResponse([], $th->getMessage());
        }

    }

    public function exchangeAmount(Request $request)
    {
        try {
            return $this->successResponse(
                $this->service->getDirectionAmount(
                    $request->from_currency, 
                    $request->to_currency, 
                    $request->send_amount, 
                    $request->get_amount
                )
            );
        } catch (Exception $th) {
            return $this->unprocessableResponse([], $th->getMessage());
        }

    }

    public function confirmExchange(CryptoExchangeRequest $request)
    {
        try {
            return $this->successResponse(
                $this->service->confirmCryptoExchange(
                    $request->from_currency, 
                    $request->to_currency, 
                    $request->send_amount
                )
            );
        } catch (Exception $th) {
            return $this->unprocessableResponse([], $th->getMessage());
        }

    }

    public function cryptoExchangeProcess(ProcessRequest $request)
    {
        try {

            $this->service->validPairCheck(
                $request->from_currency,
                $request->to_currency,
                $request->exchange_type
            );

            if ($request->exchange_type == 'crypto_buy') {
                return $this->successResponse(
                    $this->service->gatewayPayment($request)
                );
            }

            $fileName = $this->service->uploadProofFile($request);


            $processArray = [
                'from_currency' => $request->from_currency,
                'to_currency' => $request->to_currency,
                'send_amount' => $request->send_amount,
                'send_via' => $request->send_via,
                'payment_details' => $request->payment_details,
                'attach' => $fileName,
                'receive_via' => $request->receive_via,
                'receiving_address' => $request->receiving_address,
                'user_id' => auth()->id(),
            ];

            return $this->successResponse(
                $this->service->processExchange($processArray)
            );
        } catch (Exception $th) {
            return $this->unprocessableResponse([], $th->getMessage());
        }
    }



}
