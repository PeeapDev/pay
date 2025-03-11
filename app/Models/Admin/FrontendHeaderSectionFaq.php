<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrontendHeaderSectionFaq extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'type'          => 'string',
        'parent_id'     => 'integer',
        'value'         => 'object',
        'last_edit_by'  => 'integer',
        'status'        => 'boolean',
    ];
}
