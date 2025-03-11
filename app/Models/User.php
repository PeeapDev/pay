<?php

namespace App\Models;

use App\Constants\GlobalConst;
use App\Constants\PaymentGatewayConst;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Traits\User\UserPartials;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, UserPartials;
    protected $appends = ['fullname','userImage','stringStatus','lastLogin','kycStringStatus'];
    protected $dates = ['deleted_at'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ["id"];
    protected $casts = [
        'firstname'             => 'string',
        'lastname'              => 'string',
        'username'              => 'string',
        'email'                 => 'string',
        'mobile_code'           => 'string',
        'mobile'                => 'string',
        'full_mobile'           => 'string',
        'password'              => 'string',
        'referral_id'           => 'string',
        'current_referral_level_id' => 'integer',
        'image'                 => 'string',
        'status'                => 'integer',
        'email_verified_at'     => 'datetime',
        'address'               => 'object',
        'email_verified'        => 'integer',
        'sms_verified'          => 'integer',
        'kyc_verified'          => 'integer',
        'ver_code'              => 'integer',
        'ver_code_send_at'      => 'datetime',
        'two_factor_verified'   => 'integer',
        "two_factor_status"     => "integer",
        "two_factor_secret"     => "string",
        'device_id'             => 'string',
        'sudo_customer'         => 'object',
        'sudo_account'          => 'object',
        'stripe_card_holders'   => 'object',
        'stripe_connected_account'=> 'object',
        'strowallet_customer'   => 'object',
        'remember_token'        => 'string',
        'registered_by'         => 'string',
        'deleted_at'            => 'datetime',
        'created_at'            => 'datetime',
        'updated_at'            => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */


    public function scopeSmsUnverified($query)
    {
        return $query->where('sms_verified', false);
    }
    public function scopeEmailUnverified($query)
    {
        return $query->where('email_verified', false);
    }

    public function scopeEmailVerified($query) {
        return $query->where("email_verified",true);
    }

    public function scopeKycVerified($query) {
        return $query->where("kyc_verified",GlobalConst::VERIFIED);
    }

    public function scopeKycUnverified($query)
    {
        return $query->whereNot('kyc_verified',GlobalConst::VERIFIED);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeBanned($query)
    {
        return $query->where('status', false);
    }

    public function kyc()
    {
        return $this->hasOne(UserKycData::class);
    }

    public function getFullnameAttribute()
    {

        return $this->firstname . ' ' . $this->lastname;
    }

    public function referLevel() {
        return $this->belongsTo(ReferralLevelPackage::class,'current_referral_level_id','id');
    }
    public function earnedLevels() {
        return $this->hasMany(UserEarnReferralLevel::class,'user_id');
    }
    public function referUsers() {
        return $this->hasMany(ReferredUser::class,'refer_user_id','id');
    }
    public function nextReferLevel() {
        $current_refer_level = $this->referLevel;
        $default_refer_level = ReferralLevelPackage::default()->first();
        if(!$current_refer_level && $default_refer_level) {
            return ReferralLevelPackage::default()->first();
        }else if(!$current_refer_level && !$default_refer_level) {
            $first_level = ReferralLevelPackage::first();
            if(!$first_level) return false;
            return $first_level;
        }
        $next_refer_level = ReferralLevelPackage::where('id','>',$current_refer_level->id)->orderBy('id','asc')->first();
        if(!$next_refer_level) return false;
        return $next_refer_level;
    }

    public function wallets()
    {
        return $this->hasMany(UserWallet::class);
    }
    public function getEmailStatusAttribute() {
        $status = $this->email_verified;
        $data = [
            'class' => "",
            'value' => "",
        ];
        if($status == GlobalConst::VERIFIED) {
            $data = [
                'class'     => "badge badge--success",
                'value'     => "Verified",
            ];
        }else if($status == GlobalConst::UNVERIFIED) {
            $data = [
                'class'     => "badge badge--danger",
                'value'     => "Unverified",
            ];
        }
        return (object) $data;
    }

    public function getUserImageAttribute() {
        $image = $this->image;
        if($image == null) {
            return files_asset_path('profile-default');
        }else if(filter_var($image, FILTER_VALIDATE_URL)) {
            return $image;
        }else {
            return files_asset_path("user-profile") . "/" . $image;
        }
    }

    public function passwordResets() {
        return $this->hasMany(UserPasswordReset::class,"user_id");
    }

    public function scopeGetSocial($query,$credentials) {
        return $query->where("email",$credentials);
    }

    public function getStringStatusAttribute() {
        $status = $this->status;
        $data = [
            'class' => "",
            'value' => "",
        ];
        if($status == GlobalConst::ACTIVE) {
            $data = [
                'class'     => "badge badge--success",
                'value'     => "active",
            ];
        }else if($status == GlobalConst::BANNED) {
            $data = [
                'class'     => "badge badge--danger",
                'value'     => "banned",
            ];
        }
        return (object) $data;
    }

    public function getKycStringStatusAttribute() {
        $status = $this->kyc_verified;
        $data = [
            'class' => "",
            'value' => "",
        ];
        if($status == GlobalConst::APPROVED) {
            $data = [
                'class'     => "badge badge--success",
                'value'     => "Verified",
            ];
        }else if($status == GlobalConst::PENDING) {
            $data = [
                'class'     => "badge badge--warning",
                'value'     => "Pending",
            ];
        }else if($status == GlobalConst::REJECTED) {
            $data = [
                'class'     => "badge badge--danger",
                'value'     => "Rejected",
            ];
        }else {
            $data = [
                'class'     => "badge badge--danger",
                'value'     => "Unverified",
            ];
        }
        return (object) $data;
    }

    public function loginLogs(){
        return $this->hasMany(UserLoginLog::class);
    }

    public function getLastLoginAttribute() {
        if($this->loginLogs()->count() > 0) {
            return $this->loginLogs()->get()->last()->created_at->format("H:i A, d M Y");
        }

        return "N/A";
    }
    public function qrCode()
    {
        return $this->hasOne(UserQrCode::class,'user_id');
    }
    public function wallet()
    {
        return $this->hasOne(UserWallet::class,'user_id');
    }
    public function virtual_card()
    {
        return $this->hasOne(VirtualCard::class,'user_id')->where('is_default',true);
    }
    public function virtual_card_sudo()
    {
        return $this->hasOne(SudoVirtualCard::class,'user_id')->where('is_default',true);
    }
    public function virtual_card_stripe()
    {
        return $this->hasOne(StripeVirtualCard::class,'user_id')->where('is_default',true);
    }
    public function virtual_card_strowallet()
    {
        return $this->hasOne(StrowalletVirtualCard::class,'user_id')->where('is_default',true);
    }
    public function scopeSearch($query,$data) {
        return $query->where(function($q) use ($data) {
            $q->where("username","like","%".$data."%");
        })->orWhere("email","like","%".$data."%")->orWhere("full_mobile","like","%".$data."%");
    }
    public function scopeNotAuth($query) {
        $query->whereNot("id",auth()->user()->id);
    }
    public function modelGuardName() {
        return "web";
    }
    public function depositAmount() {
        return $this->hasMany(Transaction::class)
                    ->where('type', PaymentGatewayConst::TYPEADDMONEY)
                    ->where('status', 1);
    }

}
