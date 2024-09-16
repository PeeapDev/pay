<?php

namespace Modules\CryptoExchange\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CryptoExchangeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'send_amount' => 'required',
            'from_currency' => 'required',
            'to_currency' => 'required'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function fieldNames()
    {

        return [
            'send_amount' => __('Amount'),
            'from_currency' => __('From currency'),
            'currency_id' => __('Send Currency')
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
