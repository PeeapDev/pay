<?php

namespace Modules\Investment\Http\Controllers\Admin;

use Modules\Investment\DataTables\Admin\InvestmentPlansDataTable;
use Modules\Investment\Http\Requests\{StoreInvestmentPlanRequest,
    UpdateInvestmentPlanRequest
};
use Modules\Investment\Entities\{InvestmentPlan,
    Invest
};
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use App\Http\Helpers\Common;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Exception, Cache, DB;

class InvestmentPlanController extends Controller
{
    protected $helper;

    public function __construct()
    {
        $this->helper = new Common();
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(InvestmentPlansDataTable $dataTable)
    {
        $data['menu'] = 'investments';
        $data['sub_menu'] = 'investment_plans';

        if (settings('admin_investment_plan_view') == 'List') {
            return $dataTable->render('investment::admin.investment_plan.list', $data);
        } else {
            $status = request()->has('status') ? ucfirst(request()->status) : 'Active';
            $data['status'] = $status;
            $data['plans'] = InvestmentPlan::with('currency:id,code')->where('status', $status)->paginate(15);
            return view('investment::admin.investment_plan.card', $data);
        }
    }

     /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function add()
    {
        $data['menu'] = 'investments';
        $data['sub_menu'] = 'investment_plans';
        $data['currency'] = \App\Models\Currency::where(['status' => 'Active'])->whereIn('type', ['fiat', 'crypto'])->get(['id', 'code', 'type']);

        return view('investment::admin.investment_plan.add', $data);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return 
     */
    public function store(StoreInvestmentPlanRequest $request)
    {
        if (!m_g_c_v('SU5WRVNUTUVOVF9TRUNSRVQ=') && m_aic_c_v('SU5WRVNUTUVOVF9TRUNSRVQ=')) {
            return view('addons::install', ['module' => 'SU5WRVNUTUVOVF9TRUNSRVQ=']);
        }
        // Interest time frame based on term type check
        if (!array_key_exists($request->interest_time_frame, termCount($request->term_type))) {
            $this->helper->one_time_message('error', __('Interest time frame does not exist.'));
            return back()->withInput();
        }

        $investmentPaymentMethods = \App\Models\CurrencyPaymentMethod::with('method')
            ->where('currency_id', $request->currency_id)
            ->where('activated_for', 'like', "%investment%")
            ->pluck('method_id')
            ->toArray();

        if ($request->payment_methods != null && array_diff($request->payment_methods, $investmentPaymentMethods)) {
            $this->helper->one_time_message('error', __('There are no payment options available for this currency.'));
            return back()->withInput();
        }

        try {
            DB::beginTransaction();
            $data = $this->mapRequestData($request);

            InvestmentPlan::create($data);

            DB::commit();
            $this->helper->one_time_message('success', __('Investment plan added successfully.'));
            return redirect()->route('investment_plans.list');
        } catch (Exception $e) {
            DB::rollBack();
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect()->route('investment_plans.list');
        }
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $data['menu'] = 'investments';
        $data['sub_menu'] = 'investment_plans';

        $data['investment'] = InvestmentPlan::getAll()->where('id', $id)->first();
        $data['currency'] = \App\Models\Currency::where(['status' => 'Active'])->whereIn('type', ['fiat', 'crypto'])->get(['id', 'code', 'type']);
        
        //Investment plan id exist or not checking
        if (empty($data['investment'])) {
            $this->helper->one_time_message('error', __('Investment plan not found.'));
            return redirect()->route('investment_plans.list'); 
        }

        $currencyId = $data['investment']['currency_id'];

        $data['planCurrencyType'] = \App\Models\Currency::where(['id' => $currencyId])->first('type');
        $data['preference'] = ($data['planCurrencyType']['type'] == 'fiat') ? preference('decimal_format_amount', 2) : preference('decimal_format_amount_crypto', 8);

        $data['paymentMethods'] = InvestmentPlan::investmentPaymentMethodList($currencyId);
        return view('investment::admin.investment_plan.edit', $data);    
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(UpdateInvestmentPlanRequest $request, $id)
    {
        $investment = InvestmentPlan::find($id);

        //Investment plan id exist or not checking
        if (empty($investment)) {
            $this->helper->one_time_message('error', __('Investment plan not found.'));
            return redirect()->route('investment_plans.list');
        }

        $currency_id = $investment->is_locked == 'Yes' ? $request->currency : $request->currency_id;

        $investmentPaymentMethods = \App\Models\CurrencyPaymentMethod::with('method')
            ->where('currency_id', $currency_id)
            ->where('activated_for', 'like', "%investment%")
            ->pluck('method_id')
            ->toArray();

        if ($request->payment_methods != null && array_diff($request->payment_methods, $investmentPaymentMethods)) {
            $this->helper->one_time_message('error', __('There are no payment options available for this currency.'));
            return back()->withInput();
        }

        if (!m_g_c_v('SU5WRVNUTUVOVF9TRUNSRVQ=') && m_aie_c_v('SU5WRVNUTUVOVF9TRUNSRVQ=')) {
            return view('addons::install', ['module' => 'SU5WRVNUTUVOVF9TRUNSRVQ=']);
        }

        try {

            $maximum_investors = $investment->maximum_investors > $request->maximum_investors ? $investment->maximum_investors : $request->maximum_investors;

            $maximum_limit_for_investor = $investment->maximum_limit_for_investor > $request->maximum_limit_for_investor ? $investment->maximum_limit_for_investor : $request->maximum_limit_for_investor;

            //Update all field if is_locked field is Yes
            if ($investment->is_locked != 'Yes') {

                //Interest time frame based on term type check
                if (!array_key_exists(request()->interest_time_frame, termCount($request->term_type))) {
                    $this->helper->one_time_message('error', __('Interest time frame does not exist.'));
                    return back()->withInput();
                }
                DB::beginTransaction();
                $request['maximum_investors'] = $maximum_investors;
                $request['maximum_limit_for_investor'] = $maximum_limit_for_investor;
                $data = $this->mapRequestData($request);
                $investment->update($data);
                DB::commit();
            } else {

                //Update specific fields if is_locked is Yes
                $investment->update([
                    'name' => $request->name,
                    'description' => $request->description,
                    'maximum_investors' => $maximum_investors,
                    'maximum_limit_for_investor' => $maximum_limit_for_investor,
                    'is_featured' => $request->is_featured == 'on' ? 'Yes' : 'No',
                    'status' => $request->status,
                    'payment_methods' => isset($request->payment_methods) ? implode(',', $request->payment_methods) : NULL
                ]);
            }
            $this->helper->one_time_message('success', __(':x plan updated successfully.', ['x' => $investment->name]));
            return redirect()->route('investment_plans.list');
        } catch (Exception $e) {
            DB::rollBack();
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect()->route('investment_plans.list')->withInput();
        } 
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function delete($id)
    {
        $investment = InvestmentPlan::find($id);

        //Plan id exist or not check
        if (empty($investment)) {
            if (isset(request()->ajax) && request()->ajax == true) {
                $response['title']   = __('Failed!');
                $response['alert']   = 'error';
                $response['message'] = __('Investment plan not found.');
                return $response;
            }
            $this->helper->one_time_message('error', __('Investment plan not found.'));
            return redirect()->route('investment_plans.list');
        }

        $investCount = Invest::where('investment_plan_id', $id)->count();

        if ($investCount > 0) {

            if (isset(request()->ajax) && request()->ajax == true) {
                $response['title']   = __('Failed!');
                $response['alert']   = 'error';
                $response['message'] = __('This investment plan has already been invested. It cannot be deleted.');
                return $response;
            }

            $this->helper->one_time_message('error', __('This investment plan has already been invested. It cannot be deleted.'));
            return redirect()->route('investment_plans.list');
        }

        $investment->delete();

        if (isset(request()->ajax) && request()->ajax == true) {
            $response['title']   = __('Success!');
            $response['alert']   = 'success';
            $response['message'] = __('Investment plan deleted successfully.');
            return $response;
        }
        $this->helper->one_time_message('success', __('Investment plan deleted successfully.'));
        return redirect()->route('investment_plans.list');
    }

    /**
     * Pocessing request data for store and update
     * @param object $requestData
     * @return object
     */

    private function mapRequestData($requestData)
    {
        $data = $requestData->except(['_token', 'is_locked']);
        $data['maximum_amount'] = $requestData->investment_type == 'Range' ? $requestData->maximum_amount : NULL; 
        $data['slug'] = Str::slug($requestData->name);
        $data['withdraw_after_matured'] = $requestData->withdraw_after_matured == 'on' ? 'Yes' : 'No';
        $data['capital_return_term'] = $requestData->capital_return_term == 'After Matured' ? 'After Matured' : 'Term Basis';
        $data['is_featured'] = $requestData->is_featured == 'on' ? 'Yes' : 'No';
        $data['payment_methods'] = isset($requestData->payment_methods) ? implode(',', $requestData->payment_methods) : NULL;
        return $data;
    }

    public  function getPaymentMethods(Request $request) 
    {
        $currencyId = $request->currency_id;

        $data['paymentMethods'] = InvestmentPlan::investmentPaymentMethodList($currencyId);
        $data['status'] = count($data['paymentMethods']) ? 200 : 400;
        return response()->json(['data' => $data]);
    }

    public function status()
    {
        $investment = InvestmentPlan::find(request()->id);

        if (empty($investment)) {
            $response['title']   = __('Failed!');
            $response['alert']   = 'error';
            $response['message'] = __('Investment plan not found.');
            return $response;
        }

        $statuses = ['Active', 'Inactive', 'Draft'];

        if (!in_array(request()->status, $statuses)) {
            $response['title']   = __('Failed!');
            $response['alert']   = 'error';
            $response['message'] = __('Status not valid.');
            return $response;
        }

        $investment->update([
            'status' => request()->status,
        ]);

        $response['title']   = __('Changed!');
        $response['alert']   = 'success';
        $response['message'] = __(':x plan status change to :y successfully.', ['x' => $investment->name, 'y' => request()->status]);
        return $response;
    }

    public function viewChange(Request $request)
    {
        $view = $request->view;

        \App\Models\Setting::where(['name' => 'admin_investment_plan_view', 'type' => 'investment'])->update(['name' => 'admin_investment_plan_view', 'type' => 'investment', 'value' => $view]);

        Cache::forget(config('cache.prefix') . '-settings');
    }
}
