<?php

namespace Modules\Investment\Http\Controllers\Users;

use Exception;
use Illuminate\Http\Request;
use App\Http\Helpers\Common;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Support\Renderable;
use Modules\Investment\Http\Requests\StoreInvestRequest;
use Modules\Investment\Entities\{
    InvestDetailLog, 
    InvestmentPlan, 
    Invest, 
    Profit
};

class InvestController extends Controller
{
    protected $helper, $investment;

    public function __construct()
    {
        $this->helper = new Common();
        $this->investment = new Invest();
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function list($status = 'All') {
        $status = ucfirst($status);
        $data['status'] = $status;
        $data['investments'] = $this->investment->getUserInvestmentsList($status)->paginate(20);

        return view('investment::user.invest.list', $data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (!empty(session('investInfo'))) {
            session()->forget(['investInfo', 'payeer_secret_key', 'methodData']);
        }
        //set the session for validating the action
        setActionSession();

        $planId = request()->plan_id;

        if ($planId) {

            $data['investmentPlan'] = $plan = (new InvestmentPlan())->getInvestmentPlan(['currency:id,code,symbol,type'], ['id' => $planId, 'status' => 'Active'], ['*']);

            if (empty($plan)) {
                $this->helper->one_time_message('error', __('Plan is not available right now.'));
                return redirect()->route('user.investment_plans.list');
            }

            $currencyId = $plan->currency_id;
        } else {

            $data['investmentPlans'] = $plans = InvestmentPlan::with('currency:id,code,type')->status('Active')->display_order()->get();
            $currencyId = $plans->isNotEmpty() ? $plans->first()->currency_id : null;
        }
        $data['planCount'] = InvestmentPlan::whereStatus('Active')->count();
        $currencyType = \App\Models\Currency::where(['id' => $currencyId])->first(['type']);
        $data['preference'] = ($currencyType != null && $currencyType->type == 'fiat') ? preference('decimal_format_amount', 2) : preference('decimal_format_amount_crypto', 8);

        return view('investment::user.invest.add', $data);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(StoreInvestRequest $request)
    {
        //checking action session
        actionSessionCheck();

        // Checking investment plan
        $plan = (new InvestmentPlan())->getInvestmentPlan([], ['id' => $request->plan_id, 'status' => 'Active'], ['*']);

        $planResponse = (new InvestmentPlan())->investmentPlanCheck($plan);

        if ($planResponse['status'] == 404) {
            $this->helper->one_time_message('error', $planResponse['message']);
            return redirect()->route('user.invest.create');
        }

        // Checking KYC required or not in investment settings
        $kycResponse = $this->investment->userKycCheck();

        if ($kycResponse['status'] == 400) {
            $this->helper->one_time_message('error', $kycResponse['message']);
            return redirect($kycResponse['url']);
        }

        // Checking request amount for Ranged plan
        $rangedAmountResponse = $this->investment->amountCheckForRangedPlan($plan);

        if ($rangedAmountResponse['status'] == 400) {
            $this->helper->one_time_message('error', $rangedAmountResponse['message']);
            return back()->withInput();
        }

        // Checking request amount for Fixed plan
        $fixedAmountResponse = $this->investment->amountCheckForFixedPlan($plan);

        if ($fixedAmountResponse['status'] == 400) {
            $this->helper->one_time_message('error', $fixedAmountResponse['message']);
            return back()->withInput();
        }

        // User wallet exist and balance check
        $walletBalanceResponse = $this->investment->userWalletAndBalanceCheck($plan);

        if ($walletBalanceResponse['status'] == 400) {
            $this->helper->one_time_message('error', $walletBalanceResponse['message']);
            return back();
        }

        // Maximum investors check
        $investorsResponse = $this->investment->maximumInvestorsCheck($plan);

        if ($investorsResponse['status'] == 400) {
            $this->helper->one_time_message('error', $investorsResponse['message']);
            return back();
        }

        // Maximum limit for investors check
        $investorsLimitResponse = $this->investment->investorsLimitCheck($plan);
        if ($investorsLimitResponse['status'] == 400) {
            $this->helper->one_time_message('error', $investorsLimitResponse['message']);
            return back();
        }

        $investDetails = $this->investment->mapInvestmentDetails($plan);

        session(['investInfo' => $investDetails]);
        $data['investInfo'] = $investDetails;
        return view('investment::user.invest.confirmation', $data);
    }

    public function payment()
    {
        //checking action session
        actionSessionCheck();

        if (!session()->has('investInfo')) {
            clearActionSession();
            $this->helper->one_time_message('error', __('Something went wrong.'));
            return redirect()->route('user.invest.create');
        }

        $investInfo = session('investInfo');

        $paymentData = [
            'currency_id' => $investInfo['currency_id'],
            'total' => $investInfo['amount'],
            'transaction_type' => Investment,
            'payment_type' => 'investment',
            'method' => $investInfo['payment_method'],
            'redirectUrl' => route('user.investment.successPayment'),
            'success_url' => route('user.invest.success'),
            'cancel_url' => url('invest/create?plan_id='. $investInfo['plan_id']),
            'gateway' => ($investInfo['payment_method_name'] == 'Wallet') ? 'Mts' : $investInfo['payment_method_name'],
            'user_id' => auth()->id(),
            'uuid' => $investInfo['uuid'],
            'currencyCode' => $investInfo['currency_code'],
            'investInfo' => $investInfo,
        ];

        if ($investInfo['payment_method'] == Bank) {
            $paymentData['banks'] = getBankList($investInfo['currency_id'], 'investment');
        }

        $data = array_merge($investInfo, $paymentData);

        return redirect(gatewayPaymentUrl($data));

    }

    public function successPayment()
    {
        try {

            $data = getPaymentParam(request()->params);

            isGatewayValidMethod($data['payment_method']);

            $investInfo = $data['investInfo'];
            $investInfo['bank'] = request()->bank ?? null;
            $investInfo['attachment'] = request()->attachment ?? null;

            $response = $this->investment->processInvestmentConfirmation($investInfo);

            setPaymentData($response);

            if ($response['status'] != 200) {
                if (empty($response['investId'])) {
                    session()->forget(['investInfo', 'payeer_secret_key', 'methodData']);
                    throw new Exception($response['ex']['message']);
                }
            }

            if (isset(request()->execute) && (request()->execute == 'api')) {
                return $response['investId'];
            }


            return redirect()->route('user.invest.success');

        } catch (Exception $e) {

            if (isset(request()->execute) && (request()->execute == 'api')) {
                return [
                    'status' => 401,
                    'message' => $e->getMessage(),
                ];
            }

            $this->helper->one_time_message('error', $e->getMessage());
            return redirect()->route('user.invest.create');
        }

    }

    public function success()
    {
        if (empty(session('investInfo'))) {
            return redirect()->route('user.invest.create');
        }
        $data = getPaymentData('forget');
        //clearing session
        $investInfo = session('investInfo');
        $investInfo['investId'] = $data['transaction_id'] ?? $data['investId'];
        session()->forget(['investInfo', 'payeer_secret_key', 'methodData']);
        clearActionSession();
        return view('investment::user.invest.success', compact('investInfo'));
    }

    public function detail($id)
    {
        if (!m_g_c_v('SU5WRVNUTUVOVF9TRUNSRVQ=') && m_uid_c_v('SU5WRVNUTUVOVF9TRUNSRVQ=')) {
            return view('vendor.installer.errors.user');
        }

        $data['investment'] = $investment = $this->investment->getInvestment(
            [
                'investmentPlan:id,name,interest_time_frame,interest_rate_type,interest_rate,term,term_type,capital_return_term,investment_type,amount,maximum_amount,currency_id',
                'currency:id,code,symbol',
                'paymentMethod:id,name',
            ],
            [
                'id' => $id,
                'user_id' => auth()->id(),
            ],
            ['*']
        );

        $investmentResponse = $this->investment->investmentPlanCheck($investment);

        if ($investmentResponse['status'] == 404) {
            $this->helper->one_time_message('error', $investmentResponse['message']);
            return redirect()->route('user.investment.list', 'active');
        }

        if ($investment->status == 'Active') {
            (new Profit())->processInvestmentProfit($investment, auth()->id());
        }

        $data['profits'] = (new Profit())->getProfitsList([], ['invest_id' => $investment->id, 'user_id' => auth()->id()], ['calculated_at', 'amount']);

        $data['transfers'] = (new InvestDetailLog())->getInvestDetailLogsList([], ['invest_id' => $investment->id, 'user_id' => auth()->id(), 'type' => 'Transfer'], ['description', 'amount', 'created_at']);

        return view('investment::user.invest.detail', $data);
    }

    //Get active payment methods to invest
    public function getActivePaymentMethods()
    {
        $paymentMethods = $this->investment->getInvestmentActivePaymentMethods();
        $data['paymentMethods'] = $paymentMethods;
        return response()->json(['data' => $data]);
    }

    // Get preferred decimal format according to currency type
    public function checkCurrencyType()
    {
        $data = $this->investment->checkInvestmentCurrencyType();
        return response()->json(['data' => $data]);
    }

    // Check user amount limit for investment
    public function checkInvestmentUserAmountLimit()
    {
        $success = $this->investment->checkInvestmentUserAmountLimit();
        return response()->json(['success' => $success]);
    }

    public function investmentPrintPdf($transactionId)
    {
        $data['transactionDetails'] = \App\Models\Transaction::with(['payment_method:id,name', 'currency:id,symbol,code'])->where(['id' => $transactionId])->first(['uuid', 'created_at', 'status', 'currency_id', 'payment_method_id', 'subtotal', 'charge_percentage', 'charge_fixed', 'total']);

        generatePDF('investment::user.invest.investmentPaymentPdf', 'investmentMoney_', $data);
    }
}
