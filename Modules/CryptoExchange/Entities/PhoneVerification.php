<?php

namespace Modules\CryptoExchange\Entities;

use Illuminate\Database\Eloquent\Model;

class PhoneVerification extends Model
{
	protected $table = 'phone_verification';

    protected $fillable = ['phone', 'code', 'status'];

    
}
