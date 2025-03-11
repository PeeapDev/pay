<?php

namespace App\Http\Controllers\Admin;

use App\Constants\GlobalConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Response;
use App\Models\Admin\ReferralSetting;
use App\Models\ReferralLevelPackage;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReferralSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title = __("Referral Settings");
        $referral_settings = ReferralSetting::first();
        $referral_level_packages = ReferralLevelPackage::orderBy('default','DESC')->get();
        return view('admin.sections.settings.referral.index',compact('page_title','referral_settings','referral_level_packages'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function packageStore(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'title'                 => 'required|string|max:250',
            'refers'                => 'required|numeric|gte:0',
            'deposit_amount'        => 'required|numeric|gte:0',
            'commission'            => 'required|numeric|gte:0',
            'option'                => 'required',
        ]);

        if($validator->fails()) return back()->withErrors($validator->errors())->withInput()->with('modal','package-add');

        $validated = $validator->validate();

        $default = $validated['option'];
        if( $default == true){
            $OldPackages = ReferralLevelPackage::get();
            foreach ($OldPackages as $item){
                $item->default = false;
                $item->save();
            }
        }

        try{
            ReferralLevelPackage::create([
                'title'             => $validated['title'],
                'refer_user'        => $validated['refers'],
                'deposit_amount'    => $validated['deposit_amount'],
                'commission'        => $validated['commission'],
                'default'           => $default,
            ]);
        }catch(Exception $e) {
            return back()->with(['error' => [__('Something went wrong! Please try again.')]]);
        }

        return back()->with(['success' => [__('New Package Created Successfully!')]]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function packageUpdate(Request $request) {
        $validator = Validator::make($request->all(),[
            'target'                => 'required|numeric|exists:referral_level_packages,id',
            'edit_title'            => 'required|string|max:250',
            'edit_commission'       => 'required|numeric',
            'edit_refers'           => 'required|numeric',
            'edit_deposit_amount'  => 'required|numeric',
        ]);

        if($validator->fails()) return back()->withErrors($validator)->withInput()->with('modal','edit-package');

        $validated = $validator->validate();

        $package = ReferralLevelPackage::find($validated['target']);

        try{
            $package->update([
                'title'                 => $validated['edit_title'],
                'refer_user'            => $validated['edit_refers'],
                'deposit_amount'        => $validated['edit_deposit_amount'],
                'commission'            => $validated['edit_commission'],
            ]);
        }catch(Exception $e) {
            return back()->with(['error' =>  [__('Something went wrong! Please try again.')]]);
        }

        return back()->with(['success' => [__('Information Updated Successfully!')]]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'bonus'             => 'required_if:status,1|numeric',
            'mail'              => 'required|boolean',
            'sms'              => 'required|boolean',
            'status'            => 'required|boolean',
        ]);


        $validated['wallet_type']   = GlobalConst::CURRENT_BALANCE;

        try{
            $settings = ReferralSetting::first();
            if($settings) {
                $settings->update($validated);
            }else {
                $settings = ReferralSetting::create($validated);
            }
        }catch(Exception $e) {
            return back()->with(['error' => [__('Something went wrong! Please try again.')]]);
        }

        return back()->with(['success' => [__('Settings updated successfully!')]]);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function packageDelete(Request $request) {
        $validator = Validator::make($request->all(),[
            'target'        => 'required|string|exists:referral_level_packages,id',
        ]);
        $validated = $validator->validate();
        $package = ReferralLevelPackage::where("id",$validated['target'])->first();

        try{
            $package->delete();
        }catch(Exception $e) {
            return back()->with(['error' => ['Something went wrong! Please try again.']]);
        }
        return back()->with(['success' => ['Item delete successfully!']]);
    }

    public function packageStatusUpdate(Request $request) {
        $validator = Validator::make($request->all(),[
            'data_target'       => 'required|numeric|exists:referral_level_packages,id',
            'status'            => 'required|boolean',
        ]);

        if($validator->fails()) {
            $errors = ['error' => $validator->errors() ];
            return Response::error($errors);
        }

        $validated = $validator->validate();

        if(ReferralLevelPackage::whereNot("id",$validated['data_target'])->default()->exists()) {
            $warning = ['warning' => [__("Please deselect the default package first.")]];
            return Response::warning($warning);
        }

        $package = ReferralLevelPackage::find($validated['data_target']);

        try{
            $package->update([
                'default'        => ($validated['status']) ? false : true,
            ]);
        }catch(Exception $e) {
            $errors = ['error' => [__("Something went wrong! Please try again.")] ];
            return Response::error($errors,null,500);
        }

        $success = ['success' => [__("Package status updated successfully!")]];
        return Response::success($success);
    }

}
