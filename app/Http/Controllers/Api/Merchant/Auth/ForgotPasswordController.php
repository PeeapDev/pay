<?php

namespace App\Http\Controllers\Api\Merchant\Auth;

use App\Constants\GlobalConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Api\Helpers;
use App\Models\Merchants\Merchant;
use App\Models\Merchants\MerchantPasswordReset;
use App\Notifications\Merchant\Auth\PasswordResetEmail as AuthPasswordResetEmail;
use App\Providers\Admin\BasicSettingsProvider;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Traits\AdminNotifications\AuthNotifications;

class ForgotPasswordController extends Controller
{
    use AuthNotifications;
    protected $basic_settings;

    public function __construct()
    {
        $this->basic_settings = BasicSettingsProvider::get();
    }
    public function sendCode(Request $request)
    {
        if($request->type == global_const()::PHONE){
            $mobile_code_rule = 'required';
        }else{
            $mobile_code_rule = 'nullable';
        }

        $validator = Validator::make($request->all(), [
           'type' => 'required|in:'.global_const()::PHONE.','.global_const()::EMAIL,
            'credentials' => ['required', function ($attribute, $value, $fail) use ($request) {
                if ($request->type == global_const()::PHONE && !preg_match('/^0?[0-9]{9,14}$/', $value)) {
                    $fail('The ' . $attribute . ' must be a valid phone number.');
                }
                if ($request->type == global_const()::EMAIL && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $fail('The ' . $attribute . ' must be a valid email address.');
                }
            }],
            'mobile_code'    =>  $mobile_code_rule,
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated = $validator->validate();
        if($validated['type'] == GlobalConst::PHONE){
            $mobile_code = remove_special_char($validated['mobile_code']);
            $mobile = get_mobile_number($mobile_code,remove_special_char($validated['credentials']));
            $full_mobile = $mobile_code.$mobile;
            $column = "full_mobile";
            $user = Merchant::where($column,$full_mobile)->first();

            if(!$user) {
                $error = ['error'=>[__("Merchant doesn't exists.")]];
                return Helpers::error($error);
            }

            $token = generate_unique_string("merchant_password_resets","token",80);
            $code = generate_random_code();

            try{
                MerchantPasswordReset::where("merchant_id",$user->id)->delete();
                $password_reset = MerchantPasswordReset::create([
                    'merchant_id'      => $user->id,
                    'phone'         => $full_mobile,
                    'token'         => $token,
                    'code'          => $code,
                ]);
                if($this->basic_settings->merchant_sms_notification == true){
                    try{
                        sendSms($user, 'PASS_RESET_CODE', [
                            'code' => $code
                        ]);
                    }catch(Exception $e) {}
                }
            }catch(Exception $e) {
                $error = ['error'=>[__('Something went wrong! Please try again.')]];
                return Helpers::error($error);
            }
            $message =  ['success'=>[__('You Will Get Verification Code On Your Phone')]];
            return Helpers::onlysuccess($message);
        }else{
            $column = "username";
            if(check_email($request->credentials)) $column = "email";
            $user = Merchant::where($column,$request->credentials)->first();
            if(!$user) {
                $error = ['error'=>[__("Merchant doesn't exists.")]];
                return Helpers::error($error);
            }

            $token = generate_unique_string("merchant_password_resets","token",80);
            $code = generate_random_code();

            try{
                MerchantPasswordReset::where("merchant_id",$user->id)->delete();
                $password_reset = MerchantPasswordReset::create([
                    'merchant_id'   => $user->id,
                    'email'         => $request->credentials,
                    'token'         => $token,
                    'code'          => $code,
                ]);
                if($this->basic_settings->merchant_email_notification == true){
                    try{
                        $user->notify(new AuthPasswordResetEmail($user,$password_reset));
                    }catch(Exception $e){}
                }
            }catch(Exception $e) {
                $error = ['error'=>[__('Something went wrong! Please try again.')]];
                return Helpers::error($error);
            }
            $message =  ['success'=>[__('Verification code sended to your email address.')]];
            return Helpers::onlysuccess($message);
        }
    }

    //for email
    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|numeric',
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $code = $request->code;
        $basic_settings = BasicSettingsProvider::get();
        $otp_exp_seconds = $basic_settings->merchant_otp_exp_seconds ?? 0;
        $password_reset = MerchantPasswordReset::where("code", $code)->where('email',$request->email)->first();
        if(!$password_reset) {
            $error = ['error'=>[__('Verification Otp is Invalid')]];
            return Helpers::error($error);
        }
        if(Carbon::now() >= $password_reset->created_at->addSeconds($otp_exp_seconds)) {
            foreach(MerchantPasswordReset::get() as $item) {
                if(Carbon::now() >= $item->created_at->addSeconds($otp_exp_seconds)) {
                    $item->delete();
                }
            }
            $error = ['error'=>[__('Time expired. Please try again')]];
            return Helpers::error($error);
        }

        $message =  ['success'=>[__('Your Verification is successful, Now you can recover your password')]];
        return Helpers::onlysuccess($message);
    }
    public function emailResend(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated = $validator->validate();
        $column = "username";
        if(check_email($validated['email'])) $column = "email";
        $user = Merchant::where($column,$validated['email'])->first();
        if(!$user) {
            $error = ['error'=>[__("Merchant doesn't exists.")]];
            return Helpers::error($error);
        }

        $token = generate_unique_string("merchant_password_resets","token",80);
        $code = generate_random_code();

        try{
            MerchantPasswordReset::where("merchant_id",$user->id)->delete();
            $password_reset = MerchantPasswordReset::create([
                'merchant_id'   => $user->id,
                'email'         => $validated['email'],
                'token'         => $token,
                'code'          => $code,
            ]);
            if($this->basic_settings->merchant_email_notification == true){
                try{
                    $user->notify(new AuthPasswordResetEmail($user,$password_reset));
                }catch(Exception $e){}
            }
        }catch(Exception $e) {
            $error = ['error'=>[__('Something went wrong! Please try again.')]];
            return Helpers::error($error);
        }
        $message =  ['success'=>[__('Verification code sended to your email address.')]];
        return Helpers::onlysuccess($message);
    }
    public function resetPassword(Request $request) {
        $basic_settings = BasicSettingsProvider::get();
        $passowrd_rule = "required|string|min:6|confirmed";
        if($basic_settings->merchant_secure_password) {
            $passowrd_rule = ["required",Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised(),"confirmed"];
        }
        $validator = Validator::make($request->all(),[
            'code' => 'required|numeric',
            'email' => 'required|email',
            'password'      => $passowrd_rule,
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $code = $request->code;
        $password_reset = MerchantPasswordReset::where("code",$code)->where('email',$request->email)->first();
        if(!$password_reset) {
            $error = ['error'=>[__('Invalid request')]];
            return Helpers::error($error);
        }
        try{
            $password_reset->merchant->update([
                'password'      => Hash::make($request->password),
            ]);
            $this->resetNotificationToAdmin($password_reset->merchant,"MERCHANT",'merchant_api');
            $password_reset->delete();
        }catch(Exception $e) {
                $error = ['error'=>[__('Something went wrong! Please try again.')]];
            return Helpers::error($error);
        }
        $message =  ['success'=>[__('Password reset success. Please login with new password.')]];
        return Helpers::onlysuccess($message);
    }
    //
     //for sms
     public function verifyCodeSms(Request $request)
     {
         $validator = Validator::make($request->all(), [
             'mobile_code' => 'required',
             'mobile' => 'required',
             'code' => 'required|numeric',
         ]);
         if($validator->fails()){
             $error =  ['error'=>$validator->errors()->all()];
             return Helpers::validation($error);
         }
         $validated = $validator->validate();

         $mobile_code = remove_special_char($validated['mobile_code']);
         $mobile = get_mobile_number($mobile_code,remove_special_char($validated['mobile']));
         $full_mobile = $mobile_code.$mobile;

         $code = $request->code;
         $basic_settings = BasicSettingsProvider::get();
         $otp_exp_seconds = $basic_settings->merchant_otp_exp_seconds ?? 0;
         $password_reset = MerchantPasswordReset::where("code",$code)->where('phone', $full_mobile)->first();
         if(!$password_reset) {
             $error = ['error'=>[__('Verification Otp is Invalid')]];
             return Helpers::error($error);
         }
         if(Carbon::now() >= $password_reset->created_at->addSeconds($otp_exp_seconds)) {
             foreach(MerchantPasswordReset::get() as $item) {
                 if(Carbon::now() >= $item->created_at->addSeconds($otp_exp_seconds)) {
                     $item->delete();
                 }
             }
             $error = ['error'=>[__('Time expired. Please try again')]];
             return Helpers::error($error);
         }

         $message =  ['success'=>[__('Your Verification is successful, Now you can recover your password')]];
         return Helpers::onlysuccess($message);
     }
     public function smsResend(Request $request)
     {
         $validator = Validator::make($request->all(), [
             'mobile_code' => 'required',
             'mobile' => 'required',
         ]);
         if($validator->fails()){
             $error =  ['error'=>$validator->errors()->all()];
             return Helpers::validation($error);
         }
         $validated = $validator->validate();

         $mobile_code = remove_special_char($validated['mobile_code']);
         $mobile = get_mobile_number($mobile_code,remove_special_char($validated['mobile']));
         $full_mobile = $mobile_code.$mobile;

         $column = "full_mobile";
         $user = Merchant::where($column,$full_mobile)->first();
         if(!$user) {
             $error = ['error'=>[__("Merchant doesn't exists.")]];
             return Helpers::error($error);
         }

         $token = generate_unique_string("merchant_password_resets","token",80);
         $code = generate_random_code();

         try{
             MerchantPasswordReset::where("merchant_id",$user->id)->delete();
             $password_reset = MerchantPasswordReset::create([
                 'merchant_id'    => $user->id,
                 'phone'         => $full_mobile,
                 'token'         => $token,
                 'code'          => $code,
             ]);
             if($this->basic_settings->merchant_sms_notification == true){
                 try{
                     sendSms($password_reset->merchant, 'PASS_RESET_CODE', [
                         'code' => $code
                     ]);
                 }catch(Exception $e){}
             }
         }catch(Exception $e) {
             $error = ['error'=>[__('Something went wrong! Please try again.')]];
             return Helpers::error($error);
         }
         $message =  ['success'=>[__('Verification code resend success')]];
         return Helpers::onlysuccess($message);
     }
     public function resetPasswordSms(Request $request) {
         $basic_settings = BasicSettingsProvider::get();
         $passowrd_rule = "required|string|min:6|confirmed";
         if($basic_settings->secure_password) {
             $passowrd_rule = ["required",Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised(),"confirmed"];
         }

         $validator = Validator::make($request->all(), [
             'mobile_code' => 'required',
             'mobile' => 'required',
             'code' => 'required|numeric',
             'password'      => $passowrd_rule,
         ]);
         if($validator->fails()){
             $error =  ['error'=>$validator->errors()->all()];
             return Helpers::validation($error);
         }
         $validated = $validator->validate();

         $mobile_code = remove_special_char($validated['mobile_code']);
         $mobile = get_mobile_number($mobile_code,remove_special_char($validated['mobile']));
         $full_mobile = $mobile_code.$mobile;

         $code = $request->code;
         $password_reset = MerchantPasswordReset::where("code",$code)->where('phone',$full_mobile)->first();
         if(!$password_reset) {
             $error = ['error'=>[__('Invalid request')]];
             return Helpers::error($error);
         }
         try{
             $password_reset->merchant->update([
                 'password'      => Hash::make($request->password),
             ]);
             $this->resetNotificationToAdmin($password_reset->merchant,"MERCHANT",'merchant_api');
             $password_reset->delete();
         }catch(Exception $e) {
             $error = ['error'=>[__('Something went wrong! Please try again.')]];
             return Helpers::error($error);
         }
         $message =  ['success'=>[__('Password reset success. Please login with new password.')]];
         return Helpers::onlysuccess($message);
     }
}
