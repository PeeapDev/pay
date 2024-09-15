<?php

namespace Modules\Investment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvestmentPlanRequest extends FormRequest
{
    const TERMTYPE = ['Hour', 'Day', 'Week', 'Month', 'Year'];
    const TIMEFRAME = ['Hourly', 'Daily', 'Weekly', 'Monthly', 'Yearly'];
    const STATUS = ['Active', 'Inactive', 'Draft'];
    const INTERESTRATETYPE = ['Percent', 'Fixed', 'APR'];
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
            'name' => 'required|unique:investment_plans|max:50',
            'currency_id' => ['required', Rule::exists('currencies', 'id')->where(function ($query) {
                $query->where('status', 'Active');
            }),],
            'investment_type' => ['required', Rule::in(['Range', 'Fixed'])],
            'term' => 'required|numeric|min:1',
            'term_type' => ['required', Rule::in(self::TERMTYPE)],
            'amount' => request()->currency_type == 'fiat' ? 'required|numeric|min:1' : 'required|numeric',
            'maximum_amount' =>  request()->investment_type == 'Range' ? 'required|numeric|gt:amount' : 'nullable',
            'interest_rate_type' => ['required', Rule::in(self::INTERESTRATETYPE)],
            'interest_rate' => ['lt:amount','required','min:0','not_in:0'],
            'interest_time_frame' => ['required', Rule::in(self::TIMEFRAME)],
            'capital_return_term' => ['required', Rule::in(['Term Basis', 'After Matured'])],
            'payment_methods' => 'required',
            'maximum_investors' => 'required|integer|min:1|gte:maximum_limit_for_investor',
            'maximum_limit_for_investor' => 'required|numeric|min:1',
            'status' => ['required', Rule::in(self::STATUS)],
        ];
    }
    public function fieldNames()
    {
        return [
            'name' => __('Name'),
            'currency_id' => __('Currency'),
            'description' => __('Description'),
            'investment_type' => __('Investment Type'),
            'term' => __('Term'),
            'term_type' => __('Term Type'),
            'amount' => __('Amount'),
            'maximum_amount' => __('Maximum Amount'),
            'interest_rate_type' => __('Interest Rate Type'),
            'interest_rate' => __('Interest Rate'),
            'interest_time_frame' => __('Interest Time Frame'),
            'capital_return_term' => __('Capital Return Term'),
            'withdraw_after_matured' => __('Withdraw After Matured'),
            'maximum_investors' => __('Maximum Investors'),
            'maximum_limit_for_investor' => __('Maximum Limit For Investors'),
            'is_featured' => __('Is Featured'),
            'status' => __('Status'),
        ];
    }

    public function message()
    {
        return [
            'maximum_amount.gt' => __('Please provide maximum amount greater than amount.'),
            'name.max' => __('Name length should be maximum 50.'),
            'term.numeric' => __('Please enter any integer number.'),
            'currency_id.exist' => __(':x does not exist.'),
            'in' => __('Please provide value from the options.'),
            'amount.min' => __('Please provide a number that is more than or equal to one.'),
            "interest_rate.lt" => __('Interest rate should be less than amount.'),
            "maximum_investors.gte" => __('Maximum invest limit should be greater than or equal to max limit for a investor.')
        ];
    }
}
