<?php

namespace Modules\CryptoExchange\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'from_currency' => 'required',
            'to_currency' => 'required',
            'send_amount' => 'required',
            'exchange_type' => 'required',
            'send_via' => 'required',
            'receive_with' => 'required',
            'gateway' => 'required_if:exchange_type,crypto_buy',
            'payment_details' => 'required_if:send_via,address',
            'proof_file' => 'required_if:send_via,address',
            'receiving_address' => 'required_if:receive_via,address'
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
            'from_currency' => __('From Currency'),
            'to_currency' => __('To Currency'),
            'send_amount' => __('Send Amount'),
            'send_via' => __('Send Via'),
            'receive_via' => __('Receive Via'),
            'payment_details' => __('Payment Details'),
            'attachment' => __('Attachment'),
            'receiving_address' => __('Receiving Address'),
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

     /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'receiving_address.required_if' => __("Receiving address is required when receive crypto on external address"),
        ];
    }



}
