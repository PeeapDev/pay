<?php

namespace Modules\CryptoExchange\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReceivingInfoRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if (request()->exchange_type == 'crypto_sell') {
            return [
                'receiving_details' => 'required', 
            ];    
        } else {
            return [
                'crypto_address' => 'required', 
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
            'receiving_details' => __('Receiving Details'), 
            'crypto_address' => __('Crypto Address'),
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
