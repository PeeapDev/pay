<?php

namespace App\Models\Admin;

use App\Constants\GlobalConst;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FrontendHeaderSection extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $table = 'frontend_header_sections';

    protected $casts = [
        'type'          => 'string',
        'icon'          => 'object',
        'title'         => 'object',
        'sub_title'     => 'object',
        'last_edit_by'  => 'integer',
        'status'        => 'boolean',
    ];

    public function scopePersonal($query) {
        return $query->where("type",GlobalConst::PERSONAL);
    }

    public function scopeBusiness($query) {
        return $query->where("type",GlobalConst::BUSINESS);
    }

    public function scopeEnterPrice($query) {
        return $query->where("type",GlobalConst::ENTERPRISE);
    }

    public function scopeCompany($query) {
        return $query->where("type",GlobalConst::COMPANY);
    }

    public function pageContents()
    {
        return $this->hasMany(FrontendHeaderSectionPage::class,'parent_id','id')->where('status',1);
    }
    public function faqContents()
    {
        return $this->hasMany(FrontendHeaderSectionFaq::class,'parent_id','id')->where('status',1);
    }

    public function singlePageContent($parent_id) {
        $page = FrontendHeaderSectionPage::where('parent_id',$parent_id)->where('status',1)->first();
        return $page;
    }
    public function singleFaqContent($parent_id) {
        $faq = FrontendHeaderSectionFaq::where('parent_id',$parent_id)->where('status',1)->first();
        return $faq;
    }

}
