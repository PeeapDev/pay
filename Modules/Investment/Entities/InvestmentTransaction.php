<?php

namespace Modules\Investment\Entities;

class InvestmentTransaction
{
    private $investmentRelations = ['invest:id,investment_plan_id,estimate_profit,status', 'invest.investmentPlan:id,name,investment_type'];
    private $relations = [];

    public function __construct(private array $transactionRelations = [])
    {
        $this->relations = array_merge($this->investmentRelations, $this->transactionRelations);
    }

    public function getTransactionDetails($id)
    {
        $data['menu'] = 'transaction';
        $data['sub_menu'] = 'transactions';
        $data['transaction'] = $this->getTransaction($id);

        return $data;
    }

    public function getTransaction($id)
    {
        return \App\Models\Transaction::with($this->relations)->find($id);
    }
}