<?php

namespace Modules\Investment\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvestDetailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'invest_id', 'type', 'amount', 'description',
    ];

    public function getInvestDetailLogsList($withOptions = [], $constraints, $selectOptions)
    {
        return $this::with($withOptions)->where($constraints)->get($selectOptions);
    }
    
}
