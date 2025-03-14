<?php

namespace App\Models\Merchants;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantMailLog extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function merchant() {
        return $this->belongsTo(Merchant::class);
    }
}
