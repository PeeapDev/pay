<?php

namespace Modules\CryptoExchange\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CryptoUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'receive_with' => 'required',
            'pay_with' => 'required',
            'crypto_address' => request()->receive_with == 'address' ? 'required' : '',
            'payment_details' => request()->pay_with == 'others' ? 'required' : '',
            'proof_file' => request()->pay_with == 'others' ? 'required' : '',
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
            'payment_details' => __('Payment Details'),
            'proof_file' => __('Proof File'),
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
