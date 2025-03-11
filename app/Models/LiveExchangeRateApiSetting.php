<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveExchangeRateApiSetting extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'slug'                      => 'string',
        'provider'                  => 'string',
        'value'                     => 'object',
        'multiply_by'               =>'decimal:16',
        'currency_module'           => 'boolean',
        'payment_gateway_module'    => 'boolean',
        'status'                    => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeBanned($query)
    {
        return $query->where('status', false);
    }
    public function scopeSearch($query,$text) {
        $query->where(function($q) use ($text) {
            $q->where("provider","like","%".$text."%");
        });
    }
}
