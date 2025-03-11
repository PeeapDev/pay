<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEarnReferralLevel extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'id'                => 'integer',
        'user_id'           => 'integer',
        'referral_level_package_id' => 'integer'
    ];
    public $timestamps = true;


}
