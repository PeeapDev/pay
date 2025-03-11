<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionSetting extends Model
{
    use HasFactory;
    protected $guarded = ['id','slug'];
    protected $with = ['admin'];
    protected $casts = [
        'admin_id'                  => 'integer',
        'slug'                      => 'string',
        'title'                     => 'string',
        'fixed_charge'              => 'decimal:16',
        'percent_charge'            => 'decimal:16',
        'min_limit'                 => 'decimal:16',
        'max_limit'                 => 'decimal:16',
        'monthly_limit'             => 'decimal:16',
        'daily_limit'               => 'decimal:16',
        'status'                    => 'integer',
        'agent_fixed_commissions'   => 'decimal:16',
        'agent_percent_commissions' => 'decimal:16',
        'agent_profit'              => 'boolean',
    ];



    public function admin() {
        return $this->belongsTo(Admin::class);
    }
}
