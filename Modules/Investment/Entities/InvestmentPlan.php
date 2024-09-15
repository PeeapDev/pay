<?php

namespace Modules\Investment\Entities;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use App\Models\Model;

class InvestmentPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'currency_id', 'name', 'slug', 'description', 'investment_type', 'term', 'term_type', 'amount', 'maximum_amount', 'interest_rate', 'interest_rate_type', 'interest_time_frame', 'capital_return_term', 'withdraw_after_matured', 'maximum_investors', 'maximum_limit_for_investor', 'payment_methods', 'is_featured', 'is_locked', 'status'
    ];

    public function currency()
    {
        return $this->belongsTo(\App\Models\Currency::class, 'currency_id');
    }

    public function scopeStatus($query, $status = 'Active')
    {
        return $query->where('status', $status);
    }

    public function getInvestmentPlan($withOptions = [], $constraints, $selectOptions)
    {
        return $this::with($withOptions)->where($constraints)->first($selectOptions);
    }

    public function investmentPlanCheck($plan)
    {
        $success['status'] = 200;
        if (empty($plan)) {
            $success['status'] = 404;
            $success['message'] = __('Currently this plan is not available.');
        }
        return $success;
    }

    public function scopeDisplay_order($query)
    {
        switch (settings('schema_display')) {
            case "Latest":
                $query->orderBy('id', 'desc');
                break;
            case "Random":
                $query->inRandomOrder();
                break;
            case "Featured":
                $query->orderBy('is_featured', 'desc');
                break;
            case "Most popular":
                $query = $this->leftJoin('invests', 'investment_plans.id', '=', 'invests.investment_plan_id')
                ->select(DB::raw('investment_plans.*,count(invests.investment_plan_id) as total'))
                ->where('investment_plans.status', '=', 'Active')
                ->groupBy('investment_plans.id')
                ->orderBy('total', 'desc');
                break;
        }
        return $query;
    }

    public static function investmentPaymentMethodList($fromCurrencyId, $gatewayList = '')
    {
        $investmentPaymentMethods = \App\Models\CurrencyPaymentMethod::with('method:id')
            ->where('currency_id', $fromCurrencyId)
            ->where('activated_for', 'like', "%investment%");

        // Match plan payment methods with currency payment methods
        if ($gatewayList !== '') {
            $investmentPaymentMethods = $investmentPaymentMethods->whereIn('method_id', $gatewayList);
        }

        $investmentPaymentMethods = $investmentPaymentMethods->get();

        $paymentMethods = [];

        foreach ($investmentPaymentMethods as $investmentPaymentMethod) {
            $paymentMethods[$investmentPaymentMethod->id] = $investmentPaymentMethod->method['id'];
        }

        $paymentMethodList = \App\Models\PaymentMethod::where(['status' => 'Active'])
        ->whereIn('id', $paymentMethods)
        ->get(['id', 'name']);

        return $paymentMethodList;
    }
}
