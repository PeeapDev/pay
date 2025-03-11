<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralSetting extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'id'            => 'integer',
        'bonus'         => 'double',
        'status'        => 'boolean',
        'sms'           => 'boolean',
        'mail'          => 'boolean',
        'wallet_type'   => 'string',
    ];
}
