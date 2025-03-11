<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Api\Helpers;
use App\Http\Helpers\Response;
use App\Models\ReferralLevelPackage;
use App\Models\User;
use App\Providers\Admin\CurrencyProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferLevelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function referData()
    {

        $auth_user = Auth::user();

        $total_deposit = totalDeposit($auth_user->depositAmount);

        $refer_user_ids = $auth_user->referUsers->pluck("new_user_id");
        $refer_users = User::whereIn('id',$refer_user_ids)->select('firstname','email','full_mobile','username','referral_id','created_at')->get();
        $refer_users->makeHIdden(['userImage','stringStatus','kycStringStatus','lastLogin','fullname']);

        $account_level = ReferralLevelPackage::orderBy("id", "ASC")->get();
        $current_refer_id = $auth_user->referLevel?->id ?? "";

        $account_levels = [];
        foreach ($account_level as $key => $item) {
            $account_levels[] = [
                "id"                => $item->id,
                "title"             => $item->title,
                "refer_user"        => $item->refer_user,
                "deposit_amount"    => $item->deposit_amount,
                "commission"        => $item->commission,
                "default"           => $item->default,
                "active"            => $current_refer_id == $item->id,
                "created_at"        => $item->created_at,
                "updated_at"        => $item->updated_at,
            ];
        }

        $data =[
            'basic'         => [
                'total_deposit'     => (string) get_amount($total_deposit),
                'total_refers'      => (string) $refer_users->count(),
                'currency_code'     => get_default_currency_code(),
                'refer_code'        => $auth_user->referral_id
            ],
            'account_levels'        => $account_levels,
            'refer_users'           => $refer_users,
        ];
        $message =  ['success'=>[__('Refer Data Fetch Successfully!')]];
        return Helpers::success($data,$message);

    }


}
