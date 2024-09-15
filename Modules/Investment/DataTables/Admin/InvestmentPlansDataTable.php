<?php

namespace Modules\Investment\DataTables\Admin;

use Modules\Investment\Entities\InvestmentPlan;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Http\JsonResponse;
use App\Http\Helpers\Common;
use Illuminate\Support\Str;
use Auth;

class InvestmentPlansDataTable extends DataTable
{
    public function ajax(): JsonResponse
    {
        return datatables()
            ->eloquent($this->query())
            
            ->editColumn('name', function ($investmentPlan) {
                return $investmentPlan->name;
            })
            ->editColumn('type', function ($investmentPlan) {
                return $investmentPlan->type;
            })
            ->editColumn('amount', function ($investmentPlan) {
                return formatNumber($investmentPlan->amount, $investmentPlan->currency_id);
            })
            ->editColumn('maximum_amount', function ($investmentPlan) {
                return $investmentPlan->maximum_amount != 0 ? formatNumber($investmentPlan->maximum_amount, $investmentPlan->currency_id) : '-';
            })
            ->editColumn('currency_id', function ($investmentPlan) {
                return getColumnValue($investmentPlan->currency, 'code');
            })
            ->editColumn('termduration', function ($investmentPlan) {
                return $investmentPlan->term ? $investmentPlan->term . ' ' . Str::plural($investmentPlan->term_type,  $investmentPlan->term)  : "-";
            })
            ->editColumn('interest_rate', function ($investmentPlan) {
                
                return investmentInterestRateType($investmentPlan);
            })
            ->editColumn('interest_time_frame', function ($investmentPlan) {
                return $investmentPlan->interest_time_frame;
            })
            ->editColumn('is_featured', function ($investmentPlan) {
                return isFeatured($investmentPlan->is_featured);
            })
            ->editColumn('status', function ($investmentPlan) {
                return getStatusLabel($investmentPlan->status);
            })
            ->addColumn('action', function ($investmentPlan) {
                $edit = (Common::has_permission(auth('admin')->user()->id, 'edit_investment_plan')) ? '<a href="' . route('investment_plan.edit', $investmentPlan->id) . '" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i></a>&nbsp;' : '';
                $delete = (Common::has_permission(auth('admin')->user()->id, 'delete_investment_plan')) ? '<a href="' . route('investment_plan.delete', $investmentPlan->id) . '" class="btn btn-xs btn-danger delete-warning"><i class="fa fa-trash"></i></a>&nbsp;' : '';
                return $edit . $delete;
            })
            ->rawColumns(['action', 'status', 'is_featured'])
            ->make(true);
    }

    public function query()
    {
        $query = InvestmentPlan::with('currency:id,code');

        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn([
                'data' => 'id', 
                'name' => 'investment_plans.id', 
                'title' => __('ID'), 
                'searchable' => false, 
                'visible' => false
            ])
            ->addColumn([
                'data' => 'name', 
                'name' => 'investment_plans.name', 
                'title' => __('Name')
            ])
            ->addColumn([
                'data' => 'investment_type', 
                'name' => 'investment_plans.investment_type', 
                'title' => __('Type')
            ])
            ->addColumn([
                'data' => 'amount', 
                'name' => 'investment_plans.amount', 
                'title' => __('Amount')
            ])
            ->addColumn([
                'data' => 'maximum_amount', 
                'name' => 'investment_plans.maximum_amount', 
                'title' => __('Max Amount')
            ])
            ->addColumn([
                'data' => 'currency_id', 
                'name' => 'currency.code', 
                'title' => __('Currency')
            ])
            ->addColumn([
                'data' => 'termduration', 
                'name' => 'term', 
                'title' => __('Duration')
            ])
            ->addColumn([
                'data' => 'termduration', 
                'name' => 'term_type', 
                'visible' => false
            ])
            ->addColumn([
                'data' => 'interest_rate', 
                'name' => 'investment_plans.interest_rate', 
                'title' => __('Interest')
            ])
            ->addColumn([
                'data' => 'interest_time_frame', 
                'name' => 'investment_plans.interest_time_frame', 
                'title' => __('Profit')
            ])
            ->addColumn([
                'data' => 'is_featured', 
                'name' => 'investment_plans.is_featured', 
                'title' => __('Featured')
            ])
            ->addColumn([
                'data' => 'status', 
                'name' => 'investment_plans.status', 
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
