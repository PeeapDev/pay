<?php

namespace Modules\CryptoExchange\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CryptoBuySellSuccessRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        if (request()->exchange_type == 'crypto_buy') {

            return [
                'gateway' => 'required', 
            ];

        } else {

            return [
                'payment_details' => 'required', 
                'proof_file' => 'required', 
            ];
        }
        
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function fieldNames()
    {

        return [
            'payment_details' => __('Payment Details'), 
            'proof_file' => __('Proof File'),
            'gateway' => __('Payment Gateway'),
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
