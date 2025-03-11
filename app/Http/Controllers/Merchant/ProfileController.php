<?php

namespace App\Http\Controllers\Merchant;

use App\Constants\GlobalConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\PushNotificationHelper;
use App\Models\Admin\SetupKyc;
use App\Providers\Admin\BasicSettingsProvider;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use App\Traits\AdminNotifications\AuthNotifications;

class ProfileController extends Controller
{
    use AuthNotifications;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title = __("Merchant Profile");
        $kyc_data = SetupKyc::merchantKyc()->first();
        $user = userGuard()['user'];
        return view('merchant.sections.profile.index',compact("page_title","kyc_data","user"));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = userGuard()['user'];
        $validated = Validator::make($request->all(),[
            'firstname'     => "required|string|max:60",
            'lastname'      => "required|string|max:60",
            'business_name' => "required|string|max:60",
            'email'         =>  $user->registered_by == GlobalConst::EMAIL ? "nullable": "required|email|max:100",
            'country'       =>  $user->registered_by == GlobalConst::PHONE ? "nullable": "required|string|max:50",
            'phone_code'    =>  $user->registered_by == GlobalConst::PHONE ? "nullable": "required|string|max:20",
            'phone'         =>  $user->registered_by == GlobalConst::PHONE ? "nullable": "required|string|max:20",
            'state'         => "nullable|string|max:50",
            'city'          => "nullable|string|max:50",
            'zip_code'      => "nullable|string",
            'address'       => "nullable|string|max:250",
            'image'         => "nullable|image|mimes:jpg,png,svg,webp|max:10240",
        ])->validate();

        $validated['mobile']                = $user->registered_by == GlobalConst::PHONE ? $user->mobile : remove_special_char($validated['phone']);
        $validated['mobile_code']           = $user->registered_by == GlobalConst::PHONE ? $user->mobile_code : remove_special_char($validated['phone_code']);
        $complete_phone                     = $validated['mobile_code'] . $validated['mobile'];
        $validated['full_mobile']           = $complete_phone;
        $validated['email']                 = $user->registered_by == GlobalConst::EMAIL ? $user->email : $validated['email'];
        $validated                          = Arr::except($validated,['agree','phone_code','phone']);
        $validated['address']               = [
            'country'   => $user->registered_by == GlobalConst::PHONE ? $user->address->country ??"" :$validated['country'],
            'state'     => $validated['state'] ?? "",
            'city'      => $validated['city'] ?? "",
            'zip'       => $validated['zip_code'] ?? "",
            'address'   => $validated['address'] ?? "",
        ];

        if($request->hasFile("image")) {
            $image = upload_file($validated['image'],'merchant-profile',auth()->user()->image);
            $upload_image = upload_files_from_path_dynamic([$image['dev_path']],'merchant-profile');
            delete_file($image['dev_path']);
            $validated['image']     = $upload_image;
        }

        try{
            auth()->user()->update($validated);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__('Profile successfully updated!')]]);
    }

    public function passwordUpdate(Request $request) {
        $basic_settings = BasicSettingsProvider::get();
        $passowrd_rule = "required|string|min:6|confirmed";
        if($basic_settings->merchant_secure_password) {
            $passowrd_rule = ["required",Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised(),"confirmed"];
        }
        $request->validate([
            'current_password'      => "required|string",
            'password'              => $passowrd_rule,
        ]);

        if(!Hash::check($request->current_password,auth()->user()->password)) {
            throw ValidationException::withMessages([
                'current_password'      => 'Current password didn\'t match',
            ]);
        }

        try{
            auth()->user()->update([
                'password'  => Hash::make($request->password),
            ]);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__('Password successfully updated!')]]);

    }

    public function deleteAccount(Request $request) {
        $validator = Validator::make($request->all(),[
            'target'        => 'required',
        ]);
        $validated = $validator->validate();
        $user = auth()->user();
        //make unsubscribe
        try{
            (new PushNotificationHelper(['users' => [$user->id],'user_type' => 'merchant']))->unsubscribe();
        }catch(Exception $e) {}
        //admin notification
        $this->deleteUserNotificationToAdmin($user,"MERCHANT",'merchant');

        $user->status = false;
        $user->email_verified = false;
        $user->kyc_verified = false;
        $user->deleted_at = now();
        $user->save();
        try{
            Auth::logout();
            return redirect()->route('merchant.login')->with(['success' => [__('Your profile deleted successfully!')]]);
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
    }
}
