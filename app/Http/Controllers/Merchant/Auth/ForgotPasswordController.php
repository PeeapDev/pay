<?php

namespace App\Http\Controllers\Merchant\Auth;

use App\Constants\ExtensionConst;
use App\Constants\GlobalConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Response;
use App\Models\Merchants\Merchant;
use App\Models\Merchants\MerchantPasswordReset;
use App\Notifications\Merchant\Auth\PasswordResetEmail;
use App\Providers\Admin\BasicSettingsProvider;
use App\Providers\Admin\ExtensionProvider;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use App\Traits\AdminNotifications\AuthNotifications;
use Illuminate\Support\Facades\Session;

class ForgotPasswordController extends Controller
{
    use AuthNotifications;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $basic_settings;

     public function __construct()
     {
         $this->basic_settings = BasicSettingsProvider::get();
     }
    public function showForgotForm()
    {
        $page_title = setPageTitle(__("Forgot Password"));
        return view('merchant.auth.forgot-password.forgot',compact('page_title'));
    }
    /**
     * Send Verification code to user email/phone.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendCode(Request $request)
    {
        if($request->type == global_const()::PHONE){
            $mobile_code = 'required';
        }else{
            $mobile_code = 'nullable';
        }
        $extension = ExtensionProvider::get()->where('slug', ExtensionConst::GOOGLE_RECAPTCHA_SLUG)->first();
        $captcha_rules = "nullable";
        if($extension && $extension->status == true) {
            $captcha_rules = 'required|string|g_recaptcha_verify';
        }

        $validated = Validator::make($request->all(),[
            'type' => 'required|in:'.global_const()::PHONE.','.global_const()::EMAIL,
            'credentials' => ['required', function ($attribute, $value, $fail) use ($request) {
                if ($request->type == global_const()::PHONE && !preg_match('/^0?[0-9]{9,14}$/', $value)) {
                    $fail('The ' . $attribute . ' must be a valid phone number.');
                }
                if ($request->type == global_const()::EMAIL && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $fail('The ' . $attribute . ' must be a valid email address.');
                }
            }],
            'mobile_code'           =>  $mobile_code,
            'g-recaptcha-response'  => $captcha_rules

        ])->validate();


        if($validated['type'] == GlobalConst::PHONE){
            $mobile_code = remove_special_char($validated['mobile_code']);
            $mobile = $validated['mobile_code'] == '880' ? (int)$validated['credentials'] :  $validated['credentials'];
            $full_mobile = $mobile_code.$mobile;

            $column = "full_mobile";
            $user = Merchant::where($column,$full_mobile)->first();

            if(!$user) {
                return back()->with(['error' => [__("Merchant doesn't exists.")]]);
            }

            $token = generate_unique_string("merchant_password_resets","token",80);
            $code = generate_random_code();

            try{
                MerchantPasswordReset::where("merchant_id",$user->id)->delete();
                $password_reset = MerchantPasswordReset::create([
                    'merchant_id'   => $user->id,
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
                return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
            }

            return redirect()->route('merchant.password.verify.code',$token)->with(['success' => [__('You Will Get Verification Code On Your Phone')]]);
        }else{
            $column = "username";
            if(check_email($request->credentials)) $column = "email";
            $user = Merchant::where($column,$request->credentials)->first();
            if(!$user) {
                return back()->with(['error' => [__("Merchant doesn't exists.")]]);
            }

            $token = generate_unique_string("merchant_password_resets","token",80);
            $code = generate_random_code();

            try{
                MerchantPasswordReset::where("merchant_id",$user->id)->delete();
                $password_reset = MerchantPasswordReset::create([
                    'merchant_id'   => $user->id,
                    'email'       => $request->credentials,
                    'token'         => $token,
                    'code'          => $code,
                ]);
                if($this->basic_settings->merchant_email_notification == true){
                    try{
                        $user->notify(new PasswordResetEmail($user,$password_reset));

                    }catch(Exception $e){}
                }
            }catch(Exception $e) {
                return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
            }
            return redirect()->route('merchant.password.forgot.code.verify.form',$token)->with(['success' => [__('Verification code sended to your email address.')]]);

        }
    }
    public function showVerifyForm($token) {
        $page_title = setPageTitle(__("Verify Merchant"));
        $password_reset = MerchantPasswordReset::where("token",$token)->first();
        if(!$password_reset) return redirect()->route('merchant.password.forgot')->with(['error' => [__('Password Reset Token Expired')]]);
        $resend_time = 0;
        if(Carbon::now() <= $password_reset->created_at->addMinutes(GlobalConst::USER_PASS_RESEND_TIME_MINUTE)) {
            $resend_time = Carbon::now()->diffInSeconds($password_reset->created_at->addMinutes(GlobalConst::USER_PASS_RESEND_TIME_MINUTE));
        }
        $user_email = $password_reset->merchant->email ?? "";
        return view('merchant.auth.forgot-password.verify',compact('page_title','token','user_email','resend_time'));
    }
    /**
     * OTP Verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function verifyCode(Request $request,$token)
    {
        $request->merge(['token' => $token]);
        $validated = Validator::make($request->all(),[
            'token'         => "required|string|exists:merchant_password_resets,token",
            'code'      => "required|array",
            'code.*'    => "required|numeric",
        ])->validate();
        $code = $request->code;
        $code = implode("",$code);

        $basic_settings = BasicSettingsProvider::get();
        $otp_exp_seconds = $basic_settings->merchant_otp_exp_seconds ?? 0;

        $password_reset = MerchantPasswordReset::where("token",$token)->first();
        if(!$password_reset){
            return back()->with(['error' => [__('Verification code already used')]]);
        }
        if( $password_reset){
            if(Carbon::now() >= $password_reset->created_at->addSeconds($otp_exp_seconds)) {
                foreach(MerchantPasswordReset::get() as $item) {
                    if(Carbon::now() >= $item->created_at->addSeconds($otp_exp_seconds)) {
                        $item->delete();
                    }
                }
                return redirect()->route('merchant.password.forgot')->with(['error' => [__('Session expired. Please try again.')]]);
            }
        }
        if($password_reset->code != $code) {
            throw ValidationException::withMessages([
                'code'      => __("Verification Otp is Invalid"),
            ]);
        }
        return redirect()->route('merchant.password.forgot.reset.form',$token);
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function resendCode($token)
    {
        $password_reset = MerchantPasswordReset::where('token',$token)->first();
        if(!$password_reset) return back()->with(['error' => [__('Password Reset Token Expired')]]);
        if(Carbon::now() <= $password_reset->created_at->addMinutes(GlobalConst::USER_PASS_RESEND_TIME_MINUTE)) {
            throw ValidationException::withMessages([
                'code'      => __('You can resend the verification code after').' '.Carbon::now()->diffInSeconds($password_reset->created_at->addMinutes(GlobalConst::USER_PASS_RESEND_TIME_MINUTE)). ' '. __('seconds'),
            ]);
        }

        DB::beginTransaction();
        try{
            $update_data = [
                'code'          => generate_random_code(),
                'created_at'    => now(),
                'token'         => $token,
            ];
            DB::table('merchant_password_resets')->where('token',$token)->update($update_data);
            if($this->basic_settings->merchant_email_notification == true){
                try{
                    $password_reset->merchant->notify(new PasswordResetEmail($password_reset->merchant,(object) $update_data));
                }catch(Exception $e){ }
            }
            DB::commit();
        }catch(Exception $e) {
            DB::rollback();
            return back()->with(['error' => [__('Something went wrong! Please try again.')]]);
        }
        return redirect()->route('merchant.password.forgot.code.verify.form',$token)->with(['success' => [__('Verification code resend success')]]);

    }
    public function showResetForm($token) {
        $page_title = setPageTitle(__('Reset Password Page'));
        return view('merchant.auth.forgot-password.reset',compact('page_title','token'));
    }
    public function resetPassword(Request $request,$token) {
        $basic_settings = BasicSettingsProvider::get();
        $passowrd_rule = "required|string|min:6|confirmed";
        if($basic_settings->merchant_secure_password) {
            $passowrd_rule = ["required",Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised(),"confirmed"];
        }

        $request->merge(['token' => $token]);
        $validated = Validator::make($request->all(),[
            'token'         => "required|string|exists:merchant_password_resets,token",
            'password'      => $passowrd_rule,
        ])->validate();

        $password_reset = MerchantPasswordReset::where("token",$token)->first();
        if(!$password_reset) {
            throw ValidationException::withMessages([
                'password'      => __('Password Reset Token Expired'),
            ]);
        }
        try{
            $password_reset->merchant->update([
                'password'      => Hash::make($validated['password']),
            ]);
            $this->resetNotificationToAdmin($password_reset->merchant,"MERCHANT",'merchant');
            $password_reset->delete();
        }catch(Exception $e) {
            return back()->with(['error' => [__('Something went wrong! Please try again.')]]);
        }

        return redirect()->route('merchant.login')->with(['success' => [__('Password reset success. Please login with new password.')]]);
    }

    //==================================recovery password by mobile start==========================================
     public function smsVerifyCodeForm($token)
    {
        $page_title = __("Verify SMS");
        $password_reset = MerchantPasswordReset::where("token",$token)->first();
        if(!$password_reset) return redirect()->route('merchant.password.forgot')->with(['error' => [__('Password Reset Token Expired')]]);
        $resend_time = 0;
        if(Carbon::now() <= $password_reset->created_at->addMinutes(GlobalConst::USER_PASS_RESEND_TIME_MINUTE)) {
            $resend_time = Carbon::now()->diffInSeconds($password_reset->created_at->addMinutes(GlobalConst::USER_PASS_RESEND_TIME_MINUTE));
        }
        $user_mobile = $password_reset->merchant->full_mobile ?? "";
        return view('merchant.auth.sms-password.verify',compact('page_title','token','user_mobile','resend_time'));
    }
    public function smsResendCode($token)
    {
        $password_reset = MerchantPasswordReset::where('token',$token)->first();

        if(!$password_reset) return back()->with(['error' => [__('Password Reset Token Expired')]]);
        if(Carbon::now() <= $password_reset->created_at->addMinutes(GlobalConst::USER_PASS_RESEND_TIME_MINUTE)) {
            throw ValidationException::withMessages([
                'code'      => __('You can resend the verification code after').' '.Carbon::now()->diffInSeconds($password_reset->created_at->addMinutes(GlobalConst::USER_PASS_RESEND_TIME_MINUTE)). ' '. __('seconds'),
            ]);
        }
        DB::beginTransaction();
        try{
            $code = generate_random_code();
            $update_data = [
                'merchant_id'   => $password_reset->merchant->id,
                'phone'         => $password_reset->merchant->full_mobile,
                'token'         => $token,
                'code'          => $code,
            ];
            DB::table('merchant_password_resets')->where('token',$token)->update($update_data);
            if($this->basic_settings->merchant_sms_notification == true){
                try{
                    sendSms($password_reset->merchant, 'PASS_RESET_CODE', [
                        'code' => $code
                    ]);
                }catch(Exception $e) {}
            }
            DB::commit();
        }catch(Exception $e) {
            DB::rollback();
            return back()->with(['error' => [__('Something went wrong! Please try again.')]]);
        }
        return redirect()->route('merchant.password.verify.code',$token)->with(['success' => [__('Verification code resend success')]]);
    }
    public function smsVerifyCode(Request $request,$token){
        $request->merge(['token' => $token]);
        $validated = Validator::make($request->all(),[
            'token'     => "required|string|exists:merchant_password_resets,token",
            'code'      => "required|array",
            'code.*'    => "required|numeric",
        ])->validate();
        $code = $request->code;
        $code = implode("",$code);

        $basic_settings = BasicSettingsProvider::get();
        $otp_exp_seconds = $basic_settings->otp_exp_seconds ?? 0;

        $password_reset = MerchantPasswordReset::where("token",$token)->first();
        if(!$password_reset){
            return back()->with(['error' => [__('Invalid request')]]);
        }
        if( $password_reset){
            if(Carbon::now() >= $password_reset->created_at->addSeconds($otp_exp_seconds)) {
                foreach(MerchantPasswordReset::get() as $item) {
                    if(Carbon::now() >= $item->created_at->addSeconds($otp_exp_seconds)) {
                        $item->delete();
                    }
                }
                return redirect()->route('merchant.password.forgot')->with(['error' => [__('Session expired. Please try again.')]]);
            }
        }
        if($password_reset->code != $code) {
            throw ValidationException::withMessages([
                'code'      => __("Verification Otp is Invalid"),
            ]);
        }
        return redirect()->route('merchant.password.forgot.reset',$token)->with(['success' => [__('Sms code verified successfully')]]);
    }
    public function showResetPasswordForm($token){
        $page_title = __("Reset Password");
        return view('merchant.auth.sms-password.reset',compact('page_title','token'));
    }
    public function resetPasswordPost(Request $request,$token) {
        $basic_settings = BasicSettingsProvider::get();
        $passowrd_rule = "required|string|min:6|confirmed";
        if($basic_settings->secure_password) {
            $passowrd_rule = ["required",Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised(),"confirmed"];
        }

        $request->merge(['token' => $token]);
        $validated = Validator::make($request->all(),[
            'token'         => "required|string|exists:merchant_password_resets,token",
            'password'      => $passowrd_rule,
        ])->validate();

        $password_reset = MerchantPasswordReset::where("token",$token)->first();
        if(!$password_reset) {
            throw ValidationException::withMessages([
               'password'      => __('Password Reset Token Expired'),
            ]);
        }
        try{
            $password_reset->merchant->update([
                'password'      => Hash::make($validated['password']),
            ]);
            $this->resetNotificationToAdmin($password_reset->merchant,"MERCHANT",'merchant');
            $password_reset->delete();
        }catch(Exception $e) {
            return back()->with(['error' => [__('Something went wrong! Please try again.')]]);
        }

        return redirect()->route('merchant.login')->with(['success' => [__('Password reset success. Please login with new password.')]]);
    }

}
