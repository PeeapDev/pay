<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Response;
use App\Models\ReferralLevelPackage;
use App\Models\ReferredUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MyReferralStatusController extends Controller
{
    public function index(){
        $page_title = __("Refer Level");
        $breadcrumb = __("My Status");
        $auth_user = Auth::user();
        $account_level = ReferralLevelPackage::orderBy("id","ASC")->get();
        $refer_users = ReferredUser::where('refer_user_id', $auth_user->id)->with(['user' => function($query) {
            $query->with(['referUsers']);
        }])->paginate(10);
        return view('user.sections.referral.index',compact('page_title','auth_user','breadcrumb','account_level','refer_users'));
    }
    public function search(Request $request) {
        $validator = Validator::make($request->all(),[
            'text'  => 'required|string',
        ]);

        if($validator->fails()) {
            $error = ['error' => $validator->errors()];
            return Response::error($error,null,400);
        }
        $auth_user = Auth::user();
        $validated = $validator->validate();
        $refer_users = ReferredUser::search($validated['text'])->limit(10)->get();
        return view('user.components.search.user-search',compact(
            'refer_users',
        ));
    }
}
