<?php

namespace Modules\Investment\DataTables\Admin;

use Yajra\DataTables\Services\DataTable;
use Modules\Investment\Entities\Invest;
use Illuminate\Http\JsonResponse;
use App\Http\Helpers\Common;
use Config;


class InvestmentsDataTable extends DataTable
{
    public function ajax(): JsonResponse
    {
        return datatables()
            ->eloquent($this->query())
            ->editColumn('investment_plan_id', function ($investment) {
                return getColumnValue($investment->investmentPlan, 'name');
            })
            ->editColumn('user_id', function ($investment) {
                $sender = getColumnValue($investment->user);
                if ($sender <> '-' && Common::has_permission(auth('admin')->user()->id, 'edit_user')) {
                    return  '<a href="' . url(Config::get('adminPrefix') . '/users/edit/' . $investment->user->id) . '">' . $sender . '</a>';
                }
                return $sender;
            })
            ->editColumn('currency_id', function ($investment) {
                return getColumnValue($investment->currency, 'code');
            })
            ->editColumn('payment_method_id', function ($investment) {
                return $investment->paymentMethod?->name == 'Mts' ? __('Wallet') : getColumnValue($investment->paymentMethod, 'name');
            })
            ->editColumn('amount', function ($investment) {
                return formatNumber($investment->amount, $investment->currency_id);
            })
            ->editColumn('estimate_profit', function ($investment) {
                return formatNumber($investment->estimate_profit, $investment->currency_id);
            })
            ->editColumn('total', function ($investment) {
                return formatNumber($investment->total, $investment->currency_id);
            })
            ->editColumn('term_total', function ($investment) {
                return $investment->term_total;
            })
            ->editColumn('start_time', function ($investment) {
                return !empty($investment->start_time) ? dateFormat($investment->start_time) : '';
            })
            ->editColumn('end_time', function ($investment) {
                return !empty($investment->end_time) ? dateFormat($investment->end_time) : '';
            })
            ->editColumn('status', function ($investment) {
                return getStatusLabel($investment->status);
            })
            ->addColumn('action', function ($investment) {
                return (Common::has_permission(auth('admin')->user()->id, 'view_investment')) ? '<a href="' . route('investment.details', $investment->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i></a>&nbsp;' : '';
            })
            ->rawColumns(['action', 'status', 'user_id'])
            ->make(true);
    }

    public function query()
    {
        $status   = isset(request()->status) ? request()->status : 'all';
        $currency = isset(request()->currency) ? request()->currency : 'all';
        $pm       = isset(request()->payment_methods) ? request()->payment_methods : 'all';
        $user     = isset(request()->user_id) ? request()->user_id : null;
        $from     = isset(request()->from) && !empty(request()->from) ? setDateForDb(request()->from) : null;
        $to       = isset(request()->to) && !empty(request()->to) ? setDateForDb(request()->to) : null;
        $query = (new Invest())->getInvestmentsList($from, $to, $status, $currency, $pm, $user);
        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn([
                'data' => 'id',
                'name' => 'invests.id',
                'title' => __('ID'),
                'searchable' => false,
                'visible' => false
            ])
            ->addColumn([
                'data' => 'investment_plan_id', 
                'name' => 'investmentPlan.name', 
                'title' => __('Plan')
            ])
            ->addColumn([
                'data' => 'user_id', 
                'name' => 'user.last_name', 
                'title' => __('User'), 
                'visible' => false
            ])
            ->addColumn([
                'data' => 'user_id', 
                'name' => 'user.first_name', 
                'title' => __('User')
            ])
            ->addColumn([
                'data' => 'currency_id', 
                'name' => 'currency.code', 
                'title' => __('Currency')
            ])
            ->addColumn([
                'data' => 'payment_method_id', 
                'name' => 'paymentMethod.name', 
                'title' => __('Payment Method')
            ])
            ->addColumn([
                'data' => 'amount', 
                'name' => 'invests.amount', 
                'title' => __('Amount')
            ])
            ->addColumn([
                'data' => 'estimate_profit', 
                'name' => 'invests.estimate_profit', 
                'title' => __('Profit')
            ])
            ->addColumn([
                'data' => 'total', 
                'name' => 'invests.total', 
                'title' => __('Total')
            ])
            ->addColumn([
                'data' => 'term_total', 
                'name' => 'invests.term_total', 
                'title' => __('Total Term')
            ])
            ->addColumn([
                'data' => 'start_time', 
                'name' => 'invests.start_time', 
                'title' => __('Start Time')
            ])
            ->addColumn([
                'data' => 'end_time', 
                'name' => 'invests.end_time', 
                'title' => __('End Time')
            ])
            ->addColumn([
                'data' => 'status', 
                'name' => 'invests.status', 
                'title' => __('Status')
            ])
            ->addColumn([
                'data' => 'action', 
                'name' => 'action', 
                'title' => __('Action'), 
                'orderable' => false, 
                'searchable' => false
            ])
            ->parameters(dataTableOptions());
    }
}
