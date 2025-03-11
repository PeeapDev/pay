<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferredUser extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $casts = [
        'id'                => 'integer',
        'refer_user_id'     => 'integer',
        'new_user_id'       => 'integer',
    ];

    public function referUser() {
        return $this->belongsTo(User::class,'refer_user_id');
    }

    public function user() {
        return $this->belongsTo(User::class,'new_user_id');
    }
    public function scopeSearch($query, $data) {
        return $query->whereHas('user', function ($q) use ($data) {
            $q->where("username", "like", "%" . $data . "%")
              ->orWhere("email", "like", "%" . $data . "%")
              ->orWhere("full_mobile", "like", "%" . $data . "%");
        });
    }
}
