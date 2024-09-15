<?php

namespace Modules\Investment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvestRequest extends FormRequest
{
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
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'plan_id' => 'required',
            'user_amount' => 'required',
            'payment_method' => 'required'
        ];
    }

    public function fieldNames()
    {
        return [
            'plan_id' => __("Investment Plan"),
            'user_amount' => __("Amount"),
            'payment_method' => __("Payment Method"),
        ];
    }
    
}
