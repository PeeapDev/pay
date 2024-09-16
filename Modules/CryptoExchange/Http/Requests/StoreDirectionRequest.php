<?php

namespace Modules\CryptoExchange\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDirectionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'from_currency_id' => 'required',
            'to_currency_id' => 'required|different:from_currency_id',
            'exchange_rate' => request()->exchange_from == 'local' ? 'required|numeric|min:0|not_in:0' : 'nullable',
            'fees_percentage' => 'required|numeric|min:0',
            'fees_fixed' => 'required|numeric|min:0',
            'status' => 'required',
            'min_amount' => 'required|numeric|min:0|not_in:0|lte:max_amount',
            'max_amount' => 'required|numeric|min:0|not_in:0',
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
            'from_currency_id'    => __('From Currency'),
            'to_currency_id'      => __('To Currency'),
            'exchange_rate'       => __('Exchange Rate'),
            'fees_percentage'     => __('Charge Percentage'),
            'fees_fixed'          => __('Charge Fixed'),
            'payment_instruction' => __('Payment Instructions'),
            'status'              => __('Status'),
            'min_amount'          => __('Min Amount'),
            'max_amount'          => __('Max Amount'),
        ];
    }

    public function message()
    {
        return [
            'min_amount.lt'    =>__('Min amount has to be Smaller than Max Amount')
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
