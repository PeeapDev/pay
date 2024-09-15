<?php

namespace Modules\Investment\Http\Controllers\Users;

use Modules\Investment\Entities\InvestmentPlan;
use Illuminate\Routing\Controller;

class InvestmentPlanController extends Controller
{
    public function index()
    {
        $data = [
            'menu' => 'investment',
            'plans' => InvestmentPlan::with('currency:id,code')->status('Active')->display_order()->get()
        ];

        return view('investment::user.investment_plan.list', $data);
    }
}
