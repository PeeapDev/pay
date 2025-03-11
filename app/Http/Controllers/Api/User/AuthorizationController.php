<?php

namespace App\Http\Controllers\Api\User;

use App\Constants\GlobalConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Api\Helpers;
use App\Models\Admin\SetupKyc;
use App\Models\Agent;
use App\Models\Merchants\Merchant;
use App\Models\User;
use App\Models\UserAuthorization;
use App\Notifications\User\Auth\SendAuthorizationCode;
use App\Notifications\User\Auth\SendVerifyCode;
use App\Providers\Admin\BasicSettingsProvider;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Pusher\PushNotifications\PushNotifications;
use App\Traits\ControlDynamicInputFields;
use Illuminate\Support\Facades\Notification;

class AuthorizationController extends Controller
{
    use ControlDynamicInputFields;
    protected $basic_settings;

    public function __construct()
    {
        $this->basic_settings = BasicSettingsProvider::get();
    }
    //email
    public function sendMailCode()
    {
        $user = auth()->user();
        $resend = UserAuthorization::where("user_id",$user->id)->first();
        if( $resend){
            if(Carbon::now() <= $resend->created_at->addMinutes(GlobalConst::USER_VERIFY_RESEND_TIME_MINUTE)) {
                $error = ['error'=>[ __("You can resend the verification code after").' '.Carbon::now()->diffInSeconds($resend->created_at->addMinutes(GlobalConst::USER_VERIFY_RESEND_TIME_MINUTE)). ' '.__('seconds')]];
                return Helpers::error($error);
            }
        }

        $data = [
            'user_id'       =>  $user->id,
            'code'          => generate_random_code(),
            'token'         => generate_unique_string("user_authorizations","token",200),
            'created_at'    => now(),
        ];
        DB::beginTransaction();
        try{
            if($resend) {
                UserAuthorization::where("user_id", $user->id)->delete();
            }
            DB::table("user_authorizations")->insert($data);
            if($this->basic_settings->email_notification == true){
                try{
                    $user->notify(new SendAuthorizationCode((object) $data));
                }catch(Exception $e){}
            }
            DB::commit();
            $message =  ['success'=>[__('Verification code sended to your email address.')]];
            return Helpers::onlysuccess($message);
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
    }
    public function mailVerify(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'code' => 'required|numeric',
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $user = auth()->user();
        $code = $request->code;
        $otp_exp_sec = BasicSettingsProvider::get()->otp_exp_seconds ?? GlobalConst::DEFAULT_TOKEN_EXP_SEC;
        $auth_column = UserAuthorization::where("user_id",$user->id)->where("code",$code)->first();

        if(!$auth_column){
             $error = ['error'=>[__('The verification code does not match')]];
            return Helpers::error($error);
        }
        if($auth_column->created_at->addSeconds($otp_exp_sec) < now()) {
            $error = ['error'=>[__('Time expired. Please try again')]];
            return Helpers::error($error);
        }
        try{
            $auth_column->user->update([
                'email_verified'    => true,
            ]);
            $auth_column->delete();
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        $message =  ['success'=>[__('Account successfully verified')]];
        return Helpers::onlysuccess($message);
    }
    //sms
    public function sendPhoneCode()
    {
        $user = auth()->user();
        $resend = UserAuthorization::where("user_id",$user->id)->first();
        if( $resend){
            if(Carbon::now() <= $resend->created_at->addMinutes(GlobalConst::USER_VERIFY_RESEND_TIME_MINUTE)) {
                $error = ['error'=>[ __("You can resend the verification code after").' '.Carbon::now()->diffInSeconds($resend->created_at->addMinutes(GlobalConst::USER_VERIFY_RESEND_TIME_MINUTE)). ' '.__('seconds')]];
                return Helpers::error($error);
            }
        }

        $data = [
            'user_id'       =>  $user->id,
            'code'          => generate_random_code(),
            'token'         => generate_unique_string("user_authorizations","token",200),
            'created_at'    => now(),
        ];
        DB::beginTransaction();
        try{
            if($resend) {
                UserAuthorization::where("user_id", $user->id)->delete();
            }
            DB::table("user_authorizations")->insert($data);
            if($this->basic_settings->sms_notification == true){
                try{
                    sendSms($user,'SVER_CODE', [
                        'code' => $data['code']
                    ]);
                }catch(Exception $e){}
            }
            DB::commit();
            $message =  ['success'=>[__('Verification code resend success')]];
            return Helpers::onlysuccess($message);
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
    }
    public function phoneVerify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|numeric',
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $user = auth()->user();
        $code = $request->code;
        $otp_exp_sec = BasicSettingsProvider::get()->otp_exp_seconds ?? GlobalConst::DEFAULT_TOKEN_EXP_SEC;
        $auth_column = UserAuthorization::where("user_id",$user->id)->where("code",$code)->first();

        if(!$auth_column){
             $error = ['error'=>[__('The verification code does not match')]];
            return Helpers::error($error);
        }
        if($auth_column->created_at->addSeconds($otp_exp_sec) < now()) {
            $error = ['error'=>[__('Time expired. Please try again')]];
            return Helpers::error($error);
        }
        try{
            $auth_column->user->update([
                'sms_verified'    => true,
            ]);
            $auth_column->delete();
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        $message =  ['success'=>[__('Account successfully verified')]];
        return Helpers::onlysuccess($message);
    }

    public function verify2FACode(Request $request) {
        $validator = Validator::make($request->all(), [
            'otp' => 'required',
        ]);

        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }

        $code = $request->otp;
        $user = authGuardApi()['user'];

        if(!$user->two_factor_secret) {
            $error = ['error'=>[__('Your secret key is not stored properly. Please contact with system administrator')]];
            return Helpers::error($error);
        }

        if(google_2fa_verify_api($user->two_factor_secret,$code)) {
            $user->update([
                'two_factor_verified'   => true,
            ]);
            $message = ['success'=>[ __("Two factor verified successfully")]];
            return Helpers::onlySuccess($message);
        }
        $message = ['error'=>[ __('Failed to login. Please try again')]];
        return Helpers::error($message);
    }
    //kyc
    public function showKycFrom(){
        $user = auth()->user();
        $kyc_status = $user->kyc_verified;
        $user_kyc = SetupKyc::userKyc()->first();
        $status_info = "1==verified, 2==pending, 0==unverified; 3=rejected";
        $kyc_data = $user_kyc->fields;
        $kyc_fields = [];
        if($kyc_data) {
            $kyc_fields = array_reverse($kyc_data);
        }
        $data =[
            'status_info' => $status_info,
            'kyc_status' => $kyc_status,
            'userKyc' => $kyc_fields
        ];
        $message = ['success'=>[ __("KYC Verification")]];
        return Helpers::success($data,$message);

    }
    public function kycSubmit(Request $request){
        $user = auth()->user();
        if($user->kyc_verified == GlobalConst::VERIFIED){
            $message = ['error'=>[__('You are already KYC Verified User')]];
            return Helpers::error($message);

        }
        $user_kyc_fields = SetupKyc::userKyc()->first()->fields ?? [];
        $validation_rules = $this->generateValidationRules($user_kyc_fields);
        $validated = Validator::make($request->all(), $validation_rules);

        if ($validated->fails()) {
            $message =  ['error' => $validated->errors()->all()];
            return Helpers::error($message);
        }
        $validated = $validated->validate();
        $get_values = $this->placeValueWithFields($user_kyc_fields, $validated);
        $create = [
            'user_id'       => auth()->user()->id,
            'data'          => json_encode($get_values),
            'created_at'    => now(),
        ];

        DB::beginTransaction();
        try{
            DB::table('user_kyc_data')->updateOrInsert(["user_id" => $user->id],$create);
            $user->update([
                'kyc_verified'  => GlobalConst::PENDING,
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $user->update([
                'kyc_verified'  => GlobalConst::DEFAULT,
            ]);
            $message = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($message);
        }
        $message = ['success'=>[__('KYC information successfully submitted')]];
        return Helpers::onlysuccess($message);

    }

    //========================before registration======================================
    public function checkExist(Request $request){
        if($request->register_type == global_const()::PHONE){
            $mobile_code = 'required';
            $column = "full_mobile";
        }else{
            $mobile_code = 'nullable';
             $column = "email";
        }
        $validator = Validator::make($request->all(), [
          'register_type' => 'required|in:'.global_const()::PHONE.','.global_const()::EMAIL,
            'credentials' => ['required', function ($attribute, $value, $fail) use ($request) {
                if ($request->type == global_const()::PHONE && !preg_match('/^0?[0-9]{9,14}$/', $value)) {
                    $fail('The ' . $attribute . ' must be a valid phone number.');
                }
                if ($request->register_type == global_const()::EMAIL && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $fail('The ' . $attribute . ' must be a valid email address.');
                }
            }],
            'mobile_code'           =>  $mobile_code,
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated = $validator->validate();

        if($validated['register_type'] == global_const()::PHONE){
            $column = "full_mobile";
            $mobile_code = remove_special_char($validated['mobile_code']);
            $mobile = $validated['mobile_code'] == '880' ? (int)$validated['credentials'] :  $validated['credentials'];
            $full_mobile = $mobile_code.$mobile;
            $column_value =  $full_mobile;

        }elseif($validated['register_type'] == global_const()::EMAIL){
             $column = "email";
             $column_value =  $validated['credentials'];
        }

        $user = User::where($column,$column_value)->first();
        if($user){
            $error = ['error'=>[__('The phone number or email address you have provided is already in use.')]];
            return Helpers::validation($error);
        }
        $message = ['success'=>[__('Now,You can register')]];
        return Helpers::onlysuccess($message);

    }
    public function sendOtp(Request $request){
        $basic_settings = $this->basic_settings;
        if($basic_settings->agree_policy){
            $agree = 'required';
        }else{
            $agree = 'nullable';
        }

        if( $request->agree != 1 || $request->agree == null){
            return Helpers::error(['error' => [__('Terms Of Use & Privacy Policy Field Is Required!')]]);
        }

        if($request->register_type == global_const()::PHONE){
            $mobile_code = 'required';
        }else{
            $mobile_code = 'nullable';
        }

        $validator = Validator::make($request->all(), [
           'register_type' => 'required|in:'.global_const()::PHONE.','.global_const()::EMAIL,
            'credentials' => ['required', function ($attribute, $value, $fail) use ($request) {
                if ($request->type == global_const()::PHONE && !preg_match('/^0?[0-9]{9,14}$/', $value)) {
                    $fail('The ' . $attribute . ' must be a valid phone number.');
                }
                if ($request->register_type == global_const()::EMAIL && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $fail('The ' . $attribute . ' must be a valid email address.');
                }
            }],
            'mobile_code'           =>  $mobile_code,
            'agree'                 =>  $agree,
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated = $validator->validate();
        if($validated['register_type'] == GlobalConst::PHONE){

            $code = generate_random_code();
            $mobile_code = remove_special_char($validated['mobile_code']);
            $mobile = $validated['mobile_code'] == '880' ? (int)$validated['credentials'] :  $validated['credentials'];
            $full_mobile = $mobile_code.$mobile;
            $sms_verify_status = ($basic_settings->sms_verification == true) ? false : true;

            $exist              = User::where('full_mobile', $full_mobile)->first();
            $exists_agent       = Agent::where('full_mobile', $full_mobile)->first();
            $exists_merchant    = Merchant::where('full_mobile', $full_mobile)->first();
            if( $exist || $exists_agent || $exists_merchant){
                $message = ['error'=>[__("User already  exists, please try with another phone or email address.")]];
                return Helpers::error($message);
            }
            $data = [
                'user_id'       => 0,
                'phone'         => $full_mobile,
                'code'          => $code,
                'token'         => generate_unique_string("user_authorizations","token",200),
                'temp_data'     => json_encode([
                    'credentials'   => $mobile,
                    'mobile_code'   => $mobile_code,
                    'register_type' => $validated['register_type'],
                    'sms_verified'  => $sms_verify_status,
                ]),
                'created_at'    => now(),
            ];

            DB::beginTransaction();
            try{
                DB::table("user_authorizations")->insert($data);
                if($basic_settings->sms_notification == true && $basic_settings->sms_verification == true){
                    try{
                        sendSmsNotAuthUser($full_mobile, 'SVER_CODE', [
                            'code' => $code
                        ]);
                    }catch(Exception $e) {}
                }
                DB::commit();
            }catch(Exception $e) {
                DB::rollBack();
                $message = ['error'=>[__("Something went wrong! Please try again.")]];
                return Helpers::error($message);
            };
            $data=[
                'credentials'   =>  $mobile,
                'mobile_code'   => $mobile_code,
                'register_type' => $validated['register_type']
            ];
            $message =  ['success'=>[__('SMS Verification Code Send.')]];
            return Helpers::success($data,$message);
        }else{
            $exist              = User::where('email',$validated['credentials'])->first();
            $exists_agent       = Agent::where('email',$validated['credentials'])->first();
            $exists_merchant    = Merchant::where('email',$validated['credentials'])->first();
            if( $exist || $exists_agent || $exists_merchant){
                $message = ['error'=>[__("User already  exists, please try with another phone or email address.")]];
                return Helpers::error($message);
            }
            $code = generate_random_code();
            $email_verify_status = ($basic_settings->sms_verification == true) ? false : true;

            $data = [
                'user_id'       => 0,
                'email'         => $validated['credentials'],
                'code'          => $code,
                'token'         => generate_unique_string("user_authorizations","token",200),
                'temp_data'     => json_encode([
                    'credentials'   => $validated['credentials'],
                    'mobile_code'   => '',
                    'register_type' => $validated['register_type'],
                    'email_verified'  => $email_verify_status,
                ]),
                'created_at'    => now(),
            ];

            try{
                DB::table("user_authorizations")->insert($data);
                if($basic_settings->email_notification == true && $basic_settings->email_verification == true){
                    try{
                        Notification::route("mail",$validated['credentials'])->notify(new SendVerifyCode($validated['credentials'], $code));
                    }catch(Exception $e) {}
                }
                DB::commit();
            }catch(Exception $e) {
                DB::rollBack();
                $message = ['error'=>[__("Something went wrong! Please try again.")]];
                return Helpers::error($message);
            };
            $data=[
                'credentials'   => $validated['credentials'],
                'mobile_code'   => '',
                'register_type' => $validated['register_type']
            ];
            $message =  ['success'=>[__('Verification code sended to your email address.')]];
            return Helpers::success($data,$message);
        }
    }
    //email otp
    public function verifyEmailOtp(Request $request){
        $validator = Validator::make($request->all(), [
            'email'     => "required|email",
            'code'    => "required|max:6",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $code = $request->code;
        $otp_exp_sec = BasicSettingsProvider::get()->otp_exp_seconds ?? GlobalConst::DEFAULT_TOKEN_EXP_SEC;
        $auth_column = UserAuthorization::where("email",$request->email)->where("code",$code)->first();
        if(!$auth_column){
            $message = ['error'=>[__('The verification code does not match')]];
            return Helpers::error($message);
        }
        if($auth_column->created_at->addSeconds($otp_exp_sec) < now()) {
            $auth_column->delete();
            $message = ['error'=>[__('Verification code is expired')]];
            return Helpers::error($message);
        }
        try{
            $auth_column->delete();
        }catch(Exception $e) {
            $message = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($message);
        }
        $message = ['success'=>[__('Otp successfully verified')]];
        return Helpers::onlysuccess($message);
    }
    public function resendEmailOtp(Request $request){
        $validator = Validator::make($request->all(), [
            'email'     => "required|email",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $resend = UserAuthorization::where("email",$request->email)->first();
        if($resend){
            if(Carbon::now() <= $resend->created_at->addMinutes(GlobalConst::USER_VERIFY_RESEND_TIME_MINUTE)) {
                $message = ['error'=>[__("You can resend the verification code after").' '.Carbon::now()->diffInSeconds($resend->created_at->addMinutes(GlobalConst::USER_VERIFY_RESEND_TIME_MINUTE)). ' '.__('seconds')]];
                return Helpers::error($message);
            }
        }
        $code = generate_random_code();
        $data = [
            'user_id'       =>  0,
            'email'         => $request->email,
            'code'          => $code,
            'token'         => generate_unique_string("user_authorizations","token",200),
            'created_at'    => now(),
        ];
        DB::beginTransaction();
        try{
            $oldToken = UserAuthorization::where("email",$request->email)->get();
            if($oldToken){
                foreach($oldToken as $token){
                    $token->delete();
                }
            }
            DB::table("user_authorizations")->insert($data);
            if($this->basic_settings->email_notification == true){
                try{
                    Notification::route("mail",$request->email)->notify(new SendVerifyCode($request->email, $code));
                }catch(Exception $e){}
            }
                DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $message = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($message);
        }
        $message = ['success'=>[__('Verification code resend success')]];
        return Helpers::onlysuccess($message);
    }
    //sms otp
    public function verifySmsOtp(Request $request){
        $validator = Validator::make($request->all(), [
            'mobile_code'       => "required|min:1|max:10",
            'mobile'            => "required|min:9|max:15",
            'code'              => "required|max:6",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }

        $validated = $validator->validate();

        $mobile_code = remove_special_char($validated['mobile_code']);
        $mobile = $validated['mobile_code'] == '880' ? (int)$validated['mobile'] :  $validated['mobile'];
        $full_mobile = $mobile_code.$mobile;

        $code = $validated['code'];
        $otp_exp_sec = BasicSettingsProvider::get()->otp_exp_seconds ?? GlobalConst::DEFAULT_TOKEN_EXP_SEC;
        $auth_column = UserAuthorization::where("phone",$full_mobile)->where("code",$code)->first();

        if(!$auth_column){
            $message = ['error'=>[__('The verification code does not match')]];
            return Helpers::error($message);
        }
        if($auth_column->created_at->addSeconds($otp_exp_sec) < now()) {
            $auth_column->delete();
            $message = ['error'=>[__('Verification code is expired')]];
            return Helpers::error($message);
        }
        try{
            $auth_column->delete();
        }catch(Exception $e) {
            $message = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($message);
        }
        $message = ['success'=>[__('Otp successfully verified')]];
        return Helpers::onlysuccess($message);
    }
    public function resendSmsOtp(Request $request){
        $validator = Validator::make($request->all(), [
            'mobile_code'       => "required|min:1|max:10",
            'mobile'            => "required|min:9|max:15",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }

        $validated = $validator->validate();

        $mobile_code = remove_special_char($validated['mobile_code']);
        $mobile = $validated['mobile_code'] == '880' ? (int)$validated['mobile'] :  $validated['mobile'];
        $full_mobile = $mobile_code.$mobile;

        $resend = UserAuthorization::where("phone", $full_mobile)->first();
        if($resend){
            if(Carbon::now() <= $resend->created_at->addMinutes(GlobalConst::USER_VERIFY_RESEND_TIME_MINUTE)) {
                $message = ['error'=>[__("You can resend the verification code after").' '.Carbon::now()->diffInSeconds($resend->created_at->addMinutes(GlobalConst::USER_VERIFY_RESEND_TIME_MINUTE)). ' '.__('seconds')]];
                return Helpers::error($message);
            }
        }
        $code = generate_random_code();
        $data = [
            'user_id'       =>  0,
            'phone'         =>  $full_mobile,
            'code'          => $code,
            'token'         => generate_unique_string("user_authorizations","token",200),
            'created_at'    => now(),
        ];
        DB::beginTransaction();
        try{
            $oldToken = UserAuthorization::where("phone",$full_mobile)->get();
            if($oldToken){
                foreach($oldToken as $token){
                    $token->delete();
                }
            }
            DB::table("user_authorizations")->insert($data);
            if($this->basic_settings->sms_notification == true ){
                try{
                    sendSmsNotAuthUser($full_mobile,'SVER_CODE', [
                        'code' => $code
                ]);
                }catch(Exception $e){}
            }
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $message = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($message);
        }
        $message = ['success'=>[__('Verification code resend success')]];
        return Helpers::onlysuccess($message);
    }

    //========================before registration======================================
    //========================pusher beams registration================================
    public function pusherBeamsAuth(){
        $userID = request()->user_id ?? null;
        if(!$userID){
            $message = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($message);
        }

        $basic_settings = BasicSettingsProvider::get();
        if(!$basic_settings) {
            $message = ['error'=>[__("Basic setting not found!")]];
            return Helpers::error($message);
        }

        $notification_config = $basic_settings->push_notification_config;
        if(!$notification_config) {
            $message = ['error'=>[__("Notification configuration not found!")]];
            return Helpers::error($message);
        }

        $instance_id    = $notification_config->instance_id ?? null;
        $primary_key    = $notification_config->primary_key ?? null;
        if($instance_id == null || $primary_key == null) {
            $message = ['error'=>[__("Sorry! You have to configure first to send push notification.")]];
            return Helpers::error($message);
        }
        $beamsClient = new PushNotifications(
            array(
                "instanceId" => $notification_config->instance_id,
                "secretKey" => $notification_config->primary_key,
            )
        );
        $publisherUserId =  make_user_id_for_pusher("user", $userID);

        try{
            $beamsToken = $beamsClient->generateToken($publisherUserId);
            return response()->json($beamsToken);
        }catch(Exception $e) {
            $message = ['error'=>[__("Server Error. Failed to generate beams token.")]];
            return Helpers::error($message);
        }

    }
    //========================pusher beams registration================================
}
