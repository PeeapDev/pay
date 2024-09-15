<?php

namespace Modules\Investment\Http\Controllers\Admin;

use Modules\Investment\DataTables\Admin\InvestmentsDataTable;
use Modules\Investment\Exports\InvestmentsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Routing\Controller;
use App\Http\Helpers\Common;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB, Exception;
use Modules\Investment\Services\Email\{
    InvestmentMatureMailService,
    StatusChangeMailService
};
use Modules\Investment\Entities\{InvestDetailLog,
    InvestmentPlan,
    Profit,
    Invest
};

class InvestmentController extends Controller
{
    protected $helper;
    protected $invest;

    public function __construct()
    {
        $this->helper = new Common();
        $this->invest = new Invest();
    }

    public function index(InvestmentsDataTable $dataTable)
    {
        $data['menu']     = 'investments';
        $data['sub_menu'] = 'invests';

        $data['i_status']     = $this->invest->select('status')->groupBy('status')->get();
        $data['i_currencies'] = $this->invest->with('currency:id,code')->select('currency_id')->groupBy('currency_id')->get();
        $data['i_pm']         = $this->invest->with('paymentMethod:id,name')->select('payment_method_id')->whereNotNull('payment_method_id')->groupBy('payment_method_id')->get();

        $data['from']     = isset(request()->from) && !empty(request()->from) ? setDateForDb(request()->from) : null;
        $data['to']       = isset(request()->to) && !empty(request()->to) ? setDateForDb(request()->to) : null;
        $data['status']   = isset(request()->status) ? request()->status : 'all';
        $data['currency'] = isset(request()->currency) ? request()->currency : 'all';
        $data['pm']       = isset(request()->payment_methods) ? request()->payment_methods : 'all';
        $data['user']     = $user = isset(request()->user_id) ? request()->user_id : null;
        $data['getName']  = $this->invest->getInvestsUsersName($user);

        return $dataTable->render('investment::admin.investment.list', $data);
    }

    public function edit($id)
    {
        $data['menu'] = 'investments';
        $data['sub_menu'] = 'invests';

        $data['investment'] = $investment = Invest::with(['investmentPlan:id,name', 'currency:id,code,symbol', 'paymentMethod:id,name'])->find($id);

        if (empty($data['investment'])) {
            $this->helper->one_time_message('error', __('Investment record does not exist.'));
            return redirect()->route('investment.list');
        }
        
        $data['transaction'] = \App\Models\Transaction::where(['transaction_reference_id' => $investment->id, 'transaction_type_id' => Investment])->first();
        return view('investment::admin.investment.edit', $data);
    }

    public function update(Request $request)
    {
        if (!m_g_c_v('SU5WRVNUTUVOVF9TRUNSRVQ=') && m_ais_c_v('SU5WRVNUTUVOVF9TRUNSRVQ=')) {

            if (isset(request()->ajax) && request()->ajax == true) {
                $response['title']   = __('Failed!');
                $response['alert']   = 'verification needed';
                $response['message'] = __('Purchase code verification needed');
                return $response;
            }
        }

        try {
            DB::beginTransaction();

            if ($request->transaction_type == 'Investment') {

                $invest = Invest::with('investmentPlan:id,is_locked,term,term_type')->find($request->id);

                if (empty($invest)) {
                    if (isset(request()->ajax) && request()->ajax == true) {
                        $response['title']   = __('Failed!');
                        $response['alert']   = 'error';
                        $response['message'] = __('Investment plan not found.');
                        return $response;
                    }
                    $this->helper->one_time_message('error', __('Investment record does not exist.'));
                    return redirect()->route('investment.list');
                }

                if ($request->status == 'Pending') {
                    $this->helper->one_time_message('success', __('Investment is already Pending.'));
                } elseif ($request->status == 'Active') {
                    $invest->start_time = Carbon::now()->toDateTimeString();
                    $invest->end_time = Carbon::now()->add(optional($invest->investmentPlan)->term,  optional($invest->investmentPlan)->term_type)->toDateTimeString();
                    $invest->status = $request->status;
                    $invest->save();

                    //is_locked field update when invested on a plan
                    if (optional($invest->investmentPlan)->is_locked) {
                        $plan = InvestmentPlan::find($invest->investment_plan_id);
                        $plan->is_locked = 'Yes';
                        $plan->save();
                    }
                    $this->helper->one_time_message('success', __('Investment Updated Successfully.'));
                } elseif ($request->status == 'Cancelled') {
                    $invest->status = $request->status;
                    $invest->save();

                    //change investment transaction status
                    \App\Models\Transaction::where([
                        'transaction_reference_id' => $request->transaction_reference_id,
                        'transaction_type_id' => $request->transaction_type_id,
                    ])->update(['status' => 'Blocked',]);

                    //check payment method of this investment and if it is Mts then transfer the invested amount to user wallet
                    if ($invest->payment_method_id == Mts) {

                        $currentBalance = \App\Models\Wallet::where([
                            'user_id' => $request->user_id,
                            'currency_id' => $request->currency_id,
                        ])->select('balance')->first();

                        \App\Models\Wallet::where([
                            'user_id' => $request->user_id,
                            'currency_id' => $request->currency_id,
                        ])->update(['balance' => $currentBalance->balance + $request->amount]);
                    }

                    $this->helper->one_time_message('success', __('Investment Updated Successfully.'));
                }
            }
            DB::commit();

            (new StatusChangeMailService)->send($invest);

            if (isset(request()->ajax) && request()->ajax == true) {
                $response['title']   = __('Success!');
                $response['alert']   = 'success';
                $response['message'] = __('Investment Updated Successfully.');
                return $response;
            }
        } catch (Exception $e) {
            DB::rollBack();
            if (isset(request()->ajax) && request()->ajax == true) {
                $response['title']   = __('Failed!');
                $response['alert']   = 'error';
                $response['message'] = $e->getMessage();
                return $response;
            }
            $this->helper->one_time_message('error', $e->getMessage());
        }
        return redirect()->route('investment.list');
    }

    public function details($id)
    {
        $data['menu'] = 'investments';
        $data['sub_menu'] = 'invests';

        $data['investment'] = $investment = Invest::with([
            'investmentPlan:id,name,interest_time_frame,interest_rate_type,interest_rate,term,term_type,capital_return_term,investment_type,amount,maximum_amount,currency_id',
            'currency:id,code,symbol',
            'paymentMethod:id,name'
        ])->where('id', $id)->firstOrFail();


        if ($investment->status == 'Active') {
            (new Profit())->processInvestmentProfit($investment, $investment->user_id);
        }

        $data['profits'] = Profit::where('invest_id', $investment->id)->select('calculated_at', 'amount')->get();

        $data['transfers'] = InvestDetailLog::where(['invest_id' => $investment->id,'type' => 'Transfer'])->select('description', 'amount', 'created_at')->get();

        return view('investment::admin.investment.detail', $data);
    }

    public function investmentsUserSearch(Request $request)
    {
        $search = $request->search;
        $user = (new Invest())->getInvestsUsersResponse($search);

        $res = [
            'status' => 'fail',
        ];
        if (count($user) > 0) {
            $res = [
                'status' => 'success',
                'data' => $user,
            ];
        }
        return json_encode($res);
    }

    public function investmentsCsv()
    {
        return Excel::download(new InvestmentsExport(), 'investments_list_' . time() . '.xls');
    }

    public function investmentsPdf()
    {
        $from = !empty(request()->startfrom) ? setDateForDb(request()->startfrom) : null;
        $to = !empty(request()->endto) ? setDateForDb(request()->endto) : null;
        $status = isset(request()->status) ? request()->status : null;
        $pm = isset(request()->payment_methods) ? request()->payment_methods : null;
        $currency = isset(request()->currency) ? request()->currency : null;
        $user = isset(request()->user_id) ? request()->user_id : null;
        $data['investments'] = (new Invest())->getInvestmentsList($from, $to, $status, $currency, $pm, $user)->orderBy('id', 'desc')->get();

        if (isset($from) && isset($to)) {
            $data['date_range'] = $from . ' To ' . $to;
        } else {
            $data['date_range'] = 'N/A';
        }

        generatePDF('investment::admin.investment.investments_report_pdf', 'investments_report_', $data);
    }

    public function approveActiveInvestmentWithdrawal()
    {
        if (!m_g_c_v('SU5WRVNUTUVOVF9TRUNSRVQ=') && m_aipa_c_v('SU5WRVNUTUVOVF9TRUNSRVQ=')) {
            
            $response['title']   = __('Failed!');
            $response['alert']   = 'verification needed';
            $response['message'] = __('Purchase code verification needed');
            return $response;
        }

        try {
            DB::beginTransaction();
            $emailContent = [];
            //get all active investment
            $investments = Invest::where('status', 'Active')->get();
            // loop through each investment
            foreach ($investments as $investment) {
                //get investment plan 
                $plan = InvestmentPlan::where('id', $investment->investment_plan_id)->first();

                //get user wallet for investment plan currency
                $walletCheck = \App\Models\Wallet::where(['user_id' => $investment->user_id, 'currency_id' => $investment->currency_id])->first();

                //transfer mature investment profit
                //check investment term total and term count is equal to or not
                if ($investment->term_total == $investment->term_count) {

                    //check investment plan capital return term
                    if ($plan->capital_return_term == 'After Matured') {
                        // if wallet is empty create wallet with investment amount or wallet is not empty add investment amount to user wallet
                        if (empty($walletCheck)) {
                            $wallet              = new \App\Models\Wallet();
                            $wallet->user_id     = $investment->user_id;
                            $wallet->currency_id = $investment->currency_id;
                            $wallet->balance     = $investment->amount;
                            $wallet->save();
                        } else {
                            $walletCheck->balance = $walletCheck->balance + $investment->amount;
                            $walletCheck->save();
                        }
                    }

                    //get total profit amount from invest detail log table
                    $amount = InvestDetailLog::where(['invest_id' => $investment->id, 'type' => 'Profit', 'user_id' => $investment->user_id])->selectRaw('sum(amount) as profit_amount')->first()->profit_amount;

                    //check plan withdraw after mature for getting transfer amount
                    if ($plan->withdraw_after_matured == 'No') {
                        //get already transfer amount
                        $transferAmount = InvestDetailLog::where(['invest_id' => $investment->id, 'type' => 'Transfer', 'user_id' => $investment->user_id])->selectRaw('sum(amount) as transfer_amount')->first()->transfer_amount;

                        //if plan withdraw after mature then transferable amount will be $amount - $transferAmount
                        $amount = $amount - $transferAmount;
                    }

                    // transferable amount must be greater than zero 
                    if ($amount > 0) {
                        // insert transferable amount into invest detail log table
                        $investLog              = new InvestDetailLog();
                        $investLog->user_id     = $investment->user_id;
                        $investLog->invest_id   = $investment->id;
                        $investLog->type        = 'Transfer';
                        $investLog->amount      = $amount;
                        $investLog->description = 'Transfer';
                        $investLog->save();

                        // if wallet is empty create wallet with profit amount or wallet is not empty add profit amount to user wallet
                        if (empty($walletCheck)) {
                            $wallet              = new \App\Models\Wallet();
                            $wallet->user_id     = $investment->user_id;
                            $wallet->currency_id = $investment->currency_id;
                            $wallet->balance     = $amount;
                            $wallet->save();
                        } else {
                            $walletCheck->balance = $walletCheck->balance + $amount;
                            $walletCheck->save();
                        }
                    }
                    //change investment status to complete  
                    $investmentMature = Invest::where('id', $investment->id)->update(['status' => 'Completed']);
                    //email notification send to user for investment mature
                    if ($investmentMature) {
                        //get mature investment total transfer amount
                        $matureTransferAmount = InvestDetailLog::where(['invest_id' => $investment->id, 'type' => 'Transfer', 'user_id' => $investment->user_id])->selectRaw('sum(amount) as transfer_amount')->first()->transfer_amount;

                        $emailContent[] = [
                            'user' => getColumnValue($investment->user),
                            'uuid' => $investment->uuid,
                            'amount' => moneyFormat($investment->currency?->symbol, formatNumber($investment->amount, $investment->currency_id)),
                            'profit' => moneyFormat($investment->currency?->symbol, formatNumber($investment->estimate_profit, $investment->currency_id)),
                            'matureAmount' => moneyFormat($investment->currency?->symbol, formatNumber($matureTransferAmount, $investment->currency_id)),
                            'investTime' => dateFormat($investment->created_at, $investment->user_id),
                            'plan' => optional($investment->investmentPlan)->name,
                            'email' => optional($investment->user)->email
                        ];
                    }

                }
                //transfer term basis investment profit
                if ($investment->term_total > $investment->term_count && $plan->withdraw_after_matured == 'No') {
                    //get total profit amount from invest detail log table
                    $profitAmount = InvestDetailLog::where(['invest_id' => $investment->id, 'type' => 'Profit', 'user_id' => $investment->user_id])->selectRaw('sum(amount) as profit_amount')->first()->profit_amount;

                    //get already transfer amount from invest detail log table
                    $transferAmount = InvestDetailLog::where(['invest_id' => $investment->id, 'type' => 'Transfer', 'user_id' => $investment->user_id])->selectRaw('sum(amount) as transfer_amount')->first()->transfer_amount;

                    //check if profit minus transfer amount is greater than zero or not
                    if ($profitAmount - $transferAmount > 0) {
                        //insert  profit amount as transfer amount in invest detail log
                        $investLog              = new InvestDetailLog();
                        $investLog->user_id     = $investment->user_id;
                        $investLog->invest_id   = $investment->id;
                        $investLog->type        = 'Transfer';
                        $investLog->amount      = $profitAmount - $transferAmount;
                        $investLog->description = 'Transfer';
                        $investLog->save();
                        // if wallet is empty create wallet with profit amount or wallet is not empty add profit amount to user wallet
                        if (empty($walletCheck)) {
                            $wallet              = new \App\Models\Wallet();
                            $wallet->user_id     = $investment->user_id;
                            $wallet->currency_id = $investment->currency_id;
                            $wallet->balance     = $profitAmount - $transferAmount;
                            $wallet->save();
                        } else {
                            $walletCheck->balance = $walletCheck->balance + ($profitAmount - $transferAmount);
                            $walletCheck->save();
                        }
                    }
                }
            }
            DB::commit();
            //email send
            $investmentInfo = (new InvestmentMatureMailService)->send($emailContent);

            \Modules\Investment\Jobs\ProcessInvestmentEmail::dispatch($investmentInfo);
            
            $response['title']   = __('Success!');
            $response['alert']   = 'success';
            $response['message'] = __('Profit transfer to user wallet done.');
            return $response;
        } catch (Exception $e) {
            DB::rollBack();
            $response['title']   = __('Failed!');
            $response['alert']   = 'error';
            $response['message'] = $e->getMessage();
            return $response;
        }
    }

    
    public function updateTransaction(Request $request, $id)
    {
        $status = $request->status;

        $transaction = \App\Models\Transaction::find($id);

        if ($status == 'Pending') {
            $this->helper->one_time_message('success', __('The :x status is already :y.', ['x' => __('transaction'), 'y' => __('pending')]));
            return redirect(config('adminPrefix') . '/transactions');
        }

        if ($status == 'Success') {

            $investment = Invest::with('investmentPlan:id,is_locked,term,term_type')->find($transaction->transaction_reference_id);

            $transaction::where([
                'id' => $id,
                'transaction_type_id' => Investment,
            ])->update(['status' => $request->status]);

            if (settings('invest_start_on_admin_approval') != 'Yes') {

                $investment->start_time = Carbon::now()->toDateTimeString();
                $investment->end_time = Carbon::now()->add(optional($investment->investmentPlan)->term,  optional($investment->investmentPlan)->term_type)->toDateTimeString();
                $investment->status = 'Active';
                $investment->save();

            } else {
                $investment->status = 'Pending';
                $investment->save();
            }
        }

        $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('transaction')]));
        return redirect(config('adminPrefix').'/transactions');

    }
}
