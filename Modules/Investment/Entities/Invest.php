<?php

namespace Modules\Investment\Entities;

use Modules\Investment\Services\Email\InvestmentNotifyMailService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use App\Http\Helpers\Common;
use App\Models\Model;
use Carbon\Carbon;
use Exception;

class Invest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'investment_plan_id', 'currency_id', 'payment_method_id', 'uuid', 'amount', 'estimate_profit', 'total', 'received_amount', 'interest_rate', 'term', 'term_total', 'term_count', 'start_time', 'end_time', 'note', 'status'
    ];

    protected $helper;
    private $successStatus = 200;
    private $unauthorizedStatus = 401;
    private $badRequestStatus = 400;
    private $notFoundStatus = 404;

    public function __construct()
    {
        $this->helper = new Common();
    }

    public function currency()
    {
        return $this->belongsTo(\App\Models\Currency::class, 'currency_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(\App\Models\PaymentMethod::class, 'payment_method_id');
    }

    public function investmentPlan()
    {
        return $this->belongsTo(InvestmentPlan::class, 'investment_plan_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function transaction()
    {
        return $this->hasOne(\App\Models\Transaction::class, 'transaction_reference_id', 'id');
    }

    public function getUserInvestmentsList($status)
    {
        $conditions = $status == 'All' ? ['user_id' => auth()->id()] : ['status' => $status, 'user_id' => auth()->id()];

        return $this::with([
                'investmentPlan:id,name,interest_time_frame,interest_rate_type,interest_rate,term,term_type,currency_id',
                'currency:id,code,symbol'
            ])
            ->where($conditions)
            ->latest()
            ->select('id', 'user_id', 'investment_plan_id', 'currency_id', 'uuid', 'amount', 'estimate_profit', 'total', 'start_time', 'end_time', 'status');
    }

    public function getInvestment($withOptions = [], $constraints, $selectOptions)
    {
        return $this::with($withOptions)->where($constraints)->first($selectOptions);
    }

    public function investmentPlanCheck($investment)
    {
        $response['status'] = $this->successStatus;
        if (empty($investment)) {
            $response['status'] = $this->notFoundStatus;
            $response['message'] = __('Investment record does not exist.');
        }

        return $response;
    }

    public function userKycCheck()
    {
        $response['status'] = $this->successStatus;

        if (settings('kyc') == 'Yes') {
            if (auth()->user()->address_verified != 1 && auth()->user()->identity_verified != 1) {
                $response['status'] = $this->badRequestStatus;
                $response['message'] = __('Please confirm your identity and address verification.');
                $response['url'] = 'profile/personal-id';
            } elseif (auth()->user()->address_verified != 1) {
                $response['status'] = $this->badRequestStatus;
                $response['message'] = __('Please confirm your address verification.');
                $response['url'] = 'profile/personal-address';
            } elseif (auth()->user()->identity_verified != 1) {
                $response['status'] = $this->badRequestStatus;
                $response['message'] = __('Please confirm your identity verification.');
                $response['url'] = 'profile/personal-id';
            }
        }

        return $response;
    }

    public function amountCheckForRangedPlan($plan)
    {
        $response['status'] = $this->successStatus;
        if ($plan->investment_type == 'Range' && ($plan->amount > request()->user_amount || $plan->maximum_amount < request()->user_amount)) {
            $response['status'] = $this->badRequestStatus;
            $response['message'] = __('You can invest between :x - :y :z', ['x' => formatNumber($plan->amount, $plan->currency_id), 'y' => formatNumber($plan->maximum_amount, $plan->currency_id), 'z' => optional($plan->currency)->code]);
        }
        return $response;
    }

    public function amountCheckForFixedPlan($plan)
    {
        $response['status'] = $this->successStatus;
        if ($plan->investment_type == 'Fixed' && $plan->amount != request()->user_amount) {
            $response['status'] = $this->badRequestStatus;
            $response['message'] = __('The amount is not valid for this plan.');
        }
        return $response;
    }

    public function getPaymentMethodName()
    {
        $paymentMethod = \App\Models\PaymentMethod::where('id', request()->payment_method)->first();
        $paymentMethodName = $paymentMethod->name == 'Mts' ? 'Wallet' : $paymentMethod->name;

        return $paymentMethodName;
    }

    public function userWalletAndBalanceCheck($plan)
    {
        $response['status'] = $this->successStatus;

        $paymentMethodName = self::getPaymentMethodName();
        if ($paymentMethodName != 'Wallet') return $response;

        $userWalletBalance = \App\Models\Wallet::where(['user_id' => auth()->id(), 'currency_id' => $plan->currency_id])->first();

        if (empty($userWalletBalance)) {
            $response['status'] = $this->badRequestStatus;
            $response['message'] = __('You do not have :x wallet.', ['x' => optional($plan->currency)->code]);
            return $response;
        }

        if ($userWalletBalance->balance < request()->user_amount) {
            $response['status'] = $this->badRequestStatus;
            $response['message'] = __('You do not have sufficient balance to invest.');
            return $response;
        }

        return $response;
    }

    public function maximumInvestorsCheck($plan)
    {
        $response['status'] = $this->successStatus;
        $investors = Invest::where(['investment_plan_id' => request()->plan_id])->count();

        if ($plan->maximum_investors <= $investors) {
            $response['status'] = $this->badRequestStatus;
            $response['message'] = __('Maximum :x investors can invest on this plan.', ['x' => $plan->maximum_investors]);
        }
        return $response;
    }

    public function investorsLimitCheck($plan)
    {
        $response['status'] = $this->successStatus;
        $investorsLimit = Invest::where(['investment_plan_id' => request()->plan_id, 'user_id' => auth()->id()])->count();

        if ($plan->maximum_limit_for_investor <=  $investorsLimit) {
            $response['status'] = $this->badRequestStatus;
            $response['message'] = __('You already invested maximum :x times on this plan.', ['x' => $plan->maximum_limit_for_investor]);
        }
        return $response;
    }

    public function calculateTermTotal($plan)
    {
        $termTotal = $plan->term * termCount($plan->term_type, $plan->interest_time_frame);
        return $termTotal;
    }

    public function estimateProfitCalculation($plan)
    {
        $termTotal = self::calculateTermTotal($plan);
        if ($plan->interest_rate_type == 'Percent') {
            $estimateProfit = ((request()->user_amount * $plan->interest_rate) / 100) * $termTotal;
        } else if ($plan->interest_rate_type == 'APR') {
            $aprTerm = 'Year';
            $annualInterest = (request()->user_amount * $plan->interest_rate) / 100;
            $aprTermCount = termCount($aprTerm, $plan->interest_time_frame);
            $aprInterest = $annualInterest / $aprTermCount;
            $estimateProfit = $aprInterest * $termTotal;
        } else {
            $estimateProfit = $termTotal * $plan->interest_rate;
        }

        return $estimateProfit;
    }

    public function mapInvestmentDetails($plan)
    {
        $termTotal = self::calculateTermTotal($plan);
        $estimateProfit = self::estimateProfitCalculation($plan);
        $paymentMethodName = self::getPaymentMethodName();
        $transactionStatus = in_array(request()->payment_method, [Bank, Coinbase, Coinpayments]) ? 'Pending' : 'Active';
        $investStatus = settings('invest_start_on_admin_approval') == 'Yes' ? 'Pending' : 'Active';

        $investDetails = [
            'user_id' => request()->user_id,
            'plan_id' => request()->plan_id,
            'currency_id' => optional($plan->currency)->id,
            'currency_symbol' => optional($plan->currency)->symbol,
            'currency_code' => optional($plan->currency)->code,
            'currencyType' => optional($plan->currency)->type,
            'plan_name' => $plan->name,
            'term_type' => $plan->term_type,
            'is_locked' => $plan->is_locked,
            'amount' => request()->user_amount,
            'totalAmount' => request()->user_amount,
            'uuid' => unique_code(),
            'minimum_amount' => $plan->amount,
            'maximum_amount' => $plan->maximum_amount,
            'interest_rate' => $plan->interest_rate,
            'interest_rate_type' => $plan->interest_rate_type,
            'interest_time_frame' => $plan->interest_time_frame,
            'term' => $plan->term,
            'term_total' => $termTotal,
            'estimate_profit' =>  $estimateProfit,
            'total' => $estimateProfit + request()->user_amount,
            'payment_method' => request()->payment_method,
            'note' =>  "investment done",
            'tr_status' => $transactionStatus,
            'status' => ( $transactionStatus == 'Active' ) ? $investStatus : $transactionStatus,
            'payment_method_name' => $paymentMethodName,
        ];

        return $investDetails;
    }

    public function getInvestmentActivePaymentMethods()
    {
        $planId = request()->plan_id;
        $planCurrencyId = request()->plan_currency_id;

        // Plan payment methods list
        $paymentMethod  = InvestmentPlan::where(['id' => $planId, 'currency_id' => $planCurrencyId])->first(['payment_methods']);
        $paymentMethodList = !empty($paymentMethod) ? array_map('intval', explode(',', $paymentMethod->payment_methods)) : [];

        // Get payment methods (match both in plan and currency payment methods)
        $paymentMethods = InvestmentPlan::investmentPaymentMethodList($planCurrencyId, $paymentMethodList)->toArray();

        //if payments method is empty return it

        if (empty($paymentMethods)) {
            return $paymentMethods;
        }

        // User wallet based on plan currency id
        $wallet = \App\Models\Wallet::with('currency:id,type,logo,code,status')->where(['user_id' => auth()->user()->id, 'currency_id' => $planCurrencyId])->first(['id', 'currency_id', 'balance']);

        // Key - for checking if wallet not exist Mts will be unset, if exist Mts will be replace with the name 'Wallet'
        $key = array_search('Mts', array_column($paymentMethods, 'name'));

        if (!empty($wallet)) {
            $wallet->name = 'wallet' . ' ( ' . formatNumber($wallet->balance, $planCurrencyId) . ' ' . $wallet->currency['code'] . ' ) ';
            if ($paymentMethods[$key]['name'] == 'Mts') {
                $paymentMethods[$key]['name'] = $wallet->name;
            }
        } else {
            //exclude Mts - wallet payment method if wallet is empty
            if($paymentMethods[$key]['name'] == 'Mts') {
                unset($paymentMethods[$key]);
            }
        }

        return $paymentMethods;
    }

    public function checkInvestmentCurrencyType()
    {
        $preference = (request()->currencyType == 'fiat') ? preference('decimal_format_amount', 2) : preference('decimal_format_amount_crypto', 8);

        $data['amount'] = number_format((float) request()->amount, $preference, '.', '');
        $data['maximumAmount'] = number_format((float) request()->maximum_amount, $preference, '.', '');

        return $data;
    }

    public function checkInvestmentUserAmountLimit()
    {
        $success['status']  = $this->unauthorizedStatus;

        $userAmount    = request()->user_amount;
        $planType      = request()->plan_type;
        $paymentMethod = request()->payment_method;
        $planId        = request()->plan_id;

        $plan = (new InvestmentPlan())->getInvestmentPlan(['currency:id,code,symbol'], ['id' => $planId], ['*']);

        if (empty($plan)) {
            return response()->json(['success' => $success]);
        }

        $amount = $plan->amount;
        $maximumAmount = $plan->maximum_amount;

        $selectedPlanWallet = \App\Models\Wallet::with('currency:id,code,symbol')->where(['user_id' => auth()->id(), 'currency_id' => $plan->currency_id])->first();

        $selectedPlanWalletBalance = 0;
        if (!empty($selectedPlanWallet)) {
            $selectedPlanWalletBalance = $selectedPlanWallet->balance;
        }

        if ($planType == 'Range') {
            if ($userAmount <= 0) {
                $success['message'] = __('Minimum amount should be greater than zero.');
            } elseif ($userAmount < $amount || $userAmount > $maximumAmount) {
                $success['message'] = __('You can invest between :x - :y :z', ['x' => formatNumber($amount, $plan->currency_id), 'y' => formatNumber($maximumAmount, $plan->currency_id), 'z' =>  optional($plan->currency)->code]);
            } elseif (str_contains($paymentMethod, 'wallet') && $userAmount > $selectedPlanWalletBalance) {
                $success['balanceLimitError'] = __('You do not have sufficient balance to invest.');
            } else {
                $success['status'] = $this->successStatus;
            }
        } else {
            if (str_contains($paymentMethod, "wallet") && $selectedPlanWalletBalance < $plan->amount) {
                $success['balanceLimitError'] = __('You do not have sufficient balance to invest.');
            } else {
                $success['status'] = $this->successStatus;
            }
        }

        return $success;
    }

    public function getInvestsUsersName($user)
    {
        return $this->leftJoin('users', 'users.id', '=', 'invests.user_id')
            ->where(['user_id' => $user])
            ->select('users.first_name', 'users.last_name', 'users.id')
            ->first();
    }

    public function getInvestsUsersResponse($search)
    {
        return $this->leftJoin('users', 'users.id', '=', 'invests.user_id')
            ->where('users.first_name', 'LIKE', '%' . $search . '%')
            ->orWhere('users.last_name', 'LIKE', '%' . $search . '%')
            ->distinct('users.first_name')
            ->select('users.first_name', 'users.last_name', 'invests.user_id')
            ->get();
    }

    public function getInvestmentsList($from, $to, $status, $currency, $pm, $user)
    {
        $conditions = [];

        if (empty($from) || empty($to)) {
            $date_range = null;
        } else if (empty($from)) {
            $date_range = null;
        } else if (empty($to)) {
            $date_range = null;
        } else {
            $date_range = 'Available';
        }

        if (!empty($status) && $status != 'all') {
            $conditions['status'] = $status;
        }
        if (!empty($pm) && $pm != 'all') {
            $conditions['payment_method_id'] = $pm;
        }
        if (!empty($currency) && $currency != 'all') {
            $conditions['currency_id'] = $currency;
        }
        if (!empty($user)) {
            $conditions['user_id'] = $user;
        }

        $invests = $this->with([
            'user:id,first_name,last_name',
            'currency:id,code',
            'paymentMethod:id,name',
            'investmentPlan:id,name',
        ])->where($conditions);

        if (!empty($date_range)) {
            $invests->where(function ($query) use ($from, $to) {
                $query->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to);
            })->select('invests.*');
        } else {
            $invests->select('invests.*');
        }
        return $invests;
    }

    public function createInvest(Array $investInfo)
    {
        $invest = new self();
        $invest->user_id = $investInfo['user_id'];
        $invest->investment_plan_id = $investInfo['plan_id'];
        $invest->currency_id = $investInfo['currency_id'];
        $invest->payment_method_id = $investInfo['payment_method_name'] == 'Wallet' ? 1 : $investInfo['payment_method'];
        $invest->uuid = $investInfo['uuid'];
        $invest->amount = $investInfo['amount'];
        $invest->estimate_profit = $investInfo['estimate_profit'];
        $invest->total = $investInfo['total'];
        $invest->interest_rate = $investInfo['interest_rate'];
        $invest->term = $investInfo['term'];
        $invest->term_total = $investInfo['term_total'];
        $invest->received_amount = 0;
        $invest->term_count = 0;
        $invest->start_time = Carbon::now()->toDateTimeString();
        $invest->end_time = Carbon::now()->add($investInfo['term'], $investInfo['term_type'])->toDateTimeString();
        $invest->status = $investInfo['status'];
        $invest->save();

        return $invest;
    }

    public function createTransaction(Array $investInfo)
    {
        $transaction  = new \App\Models\Transaction();
        $transaction->user_id  = $investInfo['user_id'];
        $transaction->currency_id  = $investInfo['currency_id'];
        $transaction->payment_method_id = $investInfo['payment_method_name'] == 'Wallet' ? 1 : $investInfo['payment_method'];
        $transaction->uuid = $investInfo['uuid'];
        $transaction->transaction_reference_id = $investInfo['transaction_reference_id'];
        $transaction->transaction_type_id  = Investment;
        $transaction->subtotal = $investInfo['amount'];
        $transaction->total =  $investInfo['amount'];
        $transaction->payment_status = $investInfo['tr_status'];
        $transaction->status = $investInfo['tr_status'];
        $transaction->bank_id  = $investInfo['bank'];
        $transaction->file_id  = $investInfo['attachment'];
        $transaction->save();

        return $transaction;
    }

    public function createInvestDetailLog(Array $investInfo, Object $invest)
    {
        $investDetails = new InvestDetailLog();
        $investDetails->user_id = $investInfo['user_id'];
        $investDetails->invest_id = $invest->id;
        $investDetails->type = 'Invest';
        $investDetails->amount = $invest->amount;
        $investDetails->description = 'Invest on ' . $investInfo['plan_name'] . ' plan';
        $investDetails->save();
    }

    public function updateWallet($updateBalanceInfo)
    {
        //User amount deduct from wallet balance
        $walletBalance = \App\Models\Wallet::where(['user_id' => $updateBalanceInfo['user_id'], 'currency_id' =>  $updateBalanceInfo['invested_currency_id']])->first();
        if ($updateBalanceInfo['transaction_status'] = 'Success' && $updateBalanceInfo['payment_method_name'] == 'Wallet') {
            $walletBalance->update([
                'balance' => $walletBalance->balance - $updateBalanceInfo['invested_amount']
            ]);
        }
    }

    public function processInvestmentConfirmation(Array $investInfo)
    {
        $response = ['status' => 401];
        try {
            DB::beginTransaction();

            $invest = self::createInvest($investInfo);

            $investInfo['transaction_reference_id'] = $invest->id;
            $transaction = self::createTransaction($investInfo);

            self::createInvestDetailLog($investInfo, $invest);

            if ($investInfo['payment_method'] == Mts) {
                $updateBalanceInfo = [
                    'invested_currency_id' => $invest->currency_id,
                    'invested_amount' => $invest->amount,
                    'transaction_status' => $transaction->status,
                    'payment_method_name' => $investInfo['payment_method_name'],
                    'user_id' => $investInfo['user_id']
                ];

                self::updateWallet($updateBalanceInfo);
            }

            //Is_locked field update when invested on a plan
            if ($investInfo['is_locked'] != 'Yes' &&  $invest->status == 'Active') {
                $plan = InvestmentPlan::find($investInfo['plan_id']);
                $plan->is_locked = 'Yes';
                $plan->save();
            }
            DB::commit();

            //Admin Notification
            (new InvestmentNotifyMailService())->send($invest, ['type' => 'investment', 'medium' => 'email']);

            $response = [
                'status' => 200,
                'investId' => $invest->id,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            $response['investId'] = null;
            $response['ex']['message'] = $e->getMessage();
            return $response;
        }
        return $response;
    }
}
