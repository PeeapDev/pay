<?php

namespace App\Http\Controllers\Api\User\Auth;

use App\Constants\GlobalConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Api\Helpers as ApiHelpers;
use App\Models\Admin\ReferralSetting;
use App\Models\Admin\SetupKyc;
use App\Models\Agent;
use App\Models\Merchants\Merchant;
use App\Models\User;
use App\Models\UserQrCode;
use App\Providers\Admin\BasicSettingsProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Traits\User\LoggedInUsers;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use App\Traits\User\RegisteredUsers;
use App\Traits\AdminNotifications\AuthNotifications;
use App\Traits\ControlDynamicInputFields;

class LoginController extends Controller
{
    use  LoggedInUsers ,RegisteredUsers,ControlDynamicInputFields,AuthNotifications;
    protected $basic_settings;

    public function __construct()
    {
        $this->basic_settings = BasicSettingsProvider::get();
    }
    public function login(Request $request){
        if($request->login_type == global_const()::PHONE){
            $mobile_code_rule = 'required';
            $mobile_code = remove_special_char($request->mobile_code);
            $mobile = get_mobile_number($mobile_code,remove_speacial_char($request->credentials));
            $full_mobile = $mobile_code.$mobile;
            $credentials = $full_mobile;
            $column_name = 'full_mobile';
        }else{
            $mobile_code_rule = 'nullable';
            $credentials = $request->credentials;
            $column_name = 'email';
        }
        $validator = Validator::make($request->all(), [
            'login_type' => 'required|in:'.global_const()::PHONE.','.global_const()::EMAIL,
            'credentials' => ['required', function ($attribute, $value, $fail) use ($request) {
                if ($request->type == global_const()::PHONE && !preg_match('/^0?[0-9]{9,14}$/', $value)) {
                    $fail('The ' . $attribute . ' must be a valid phone number.');
                }
                if ($request->login_type == global_const()::EMAIL && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $fail('The ' . $attribute . ' must be a valid email address.');
                }
            }],
            'mobile_code'   =>  $mobile_code_rule,
            'password'      => 'required|string|min:6',
        ]);

        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return ApiHelpers::validation($error);
        }

        $user = User::where($column_name,$credentials)->first();
        if(!$user){
            $error = ['error'=>[__("User doesn't exists.")]];
            return ApiHelpers::validation($error);
        }
        if (Hash::check($request->password, $user->password)) {
            if($user->status == 0){
                $error = ['error'=>[__('Account Has been Suspended')]];
                return ApiHelpers::validation($error);
            }
            $user->two_factor_verified = false;
            $user->save();
            $this->refreshUserWallets($user);
            $this->createLoginLog($user);
            $this->createQr($user);
            $token = $user->createToken('user_token')->accessToken;

            $data = ['token' => $token, 'user' => $user, ];
            $message =  ['success'=>[__('Login Successful')]];
            return ApiHelpers::success($data,$message);

        } else {
            $error = ['error'=>[__('Incorrect Password')]];
            return ApiHelpers::error($error);
        }

    }

    public function register(Request $request){
        $basic_settings = $this->basic_settings;
        $refer_system = ReferralSetting::first();
        $passowrd_rule = "required|string|min:6|confirmed";
        if($basic_settings->secure_password) {
            $passowrd_rule = ["required","confirmed",Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised()];
        }
        if($basic_settings->agree_policy){
            $agree = 'required';
        }else{
            $agree = '';
        }
        if( $basic_settings->email_verification){
            $email_field ='required|string|email|max:150|unique:users,email';
        }else{
            $email_field ='nullable';
        }
        if( $basic_settings->sms_verification){
            $mobile_code_field  = 'required|string|max:10';
            $mobile_field ='required|string|max:20|unique:users,mobile';
        }else{
            $mobile_code_field  ='nullable';
            $mobile_field       ='nullable';
        }
        $validator = Validator::make($request->all(), [
            'register_type' => 'required|string',
            'firstname'     => 'required|string|max:60',
            'lastname'      => 'required|string|max:60',
            'email'         => $email_field,
            'password'      => $passowrd_rule,
            'country'       => 'required|string|max:150',
            'city'          => 'required|string|max:150',
            'phone_code'    => $mobile_code_field,
            'phone'         => $mobile_field,
            'zip_code'      => 'required|string|max:8',
            'agree'         =>  $agree,
            'refer'         => 'nullable|string|exists:users,referral_id',
        ]);

        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return ApiHelpers::validation($error);
        }
        if($basic_settings->kyc_verification == true){
            $user_kyc_fields = SetupKyc::userKyc()->first()->fields ?? [];
            $validation_rules = $this->generateValidationRules($user_kyc_fields);
            $validated = Validator::make($request->all(), $validation_rules);

            if ($validated->fails()) {
                $message =  ['error' => $validated->errors()->all()];
                return ApiHelpers::error($message);
            }
            $validated = $validated->validate();
            $get_values = $this->registerPlaceValueWithFields($user_kyc_fields, $validated);
        }
        $data = $request->all();
        $mobile_code        =  remove_speacial_char($data['phone_code']??null);
        $mobile             =  get_mobile_number($mobile_code,$data['phone']??null);
        $complete_phone     =  $mobile == null ? null : $mobile_code.$mobile;


        if(User::where('full_mobile',$complete_phone)->orWhere('email',$data['email'])->exists()) {
            $error = ['error'=>[__('The phone number or email address you have provided is already in use')]];
            return ApiHelpers::validation($error);
        }
        if(Agent::where('full_mobile',$complete_phone)->orWhere('email',$data['email'])->exists()) {
            $error = ['error'=>[__('The phone number or email address you have provided is already in use')]];
            return ApiHelpers::validation($error);
        }
        if(Merchant::where('full_mobile',$complete_phone)->orWhere('email',$data['email'])->exists()) {
            $error = ['error'=>[__('The phone number or email address you have provided is already in use')]];
            return ApiHelpers::validation($error);
        }

        $userName = make_username($data['firstname'],$data['lastname']);
        $check_user_name = User::where('username',$userName)->first();
        if($check_user_name){
            $userName = $userName.'-'.rand(123,456);
        }

        //check register type
        $register_type      = $data['register_type'];
        if($register_type == GlobalConst::PHONE){
            if($basic_settings->sms_verification == false){
                $sms_verified = true;
            }elseif( $basic_settings->sms_verification == true){
                $sms_verified = true;
            }else{
                $sms_verified = false;
            }
        }elseif($basic_settings->sms_verification == false){
            $sms_verified = true;
        }else{
            $sms_verified = false;
        }


        if($register_type == GlobalConst::EMAIL){
            if ($basic_settings->email_verification == false){
                $email_verified = true;
            }elseif( $basic_settings->email_verification == true){
                $email_verified = true;
            }else{
                $email_verified = false;
            }
        }elseif($basic_settings->email_verification == false){
            $email_verified = true;
        }else{
            $email_verified = false;
        }

        //User Create
        $user = new User();
        $user->firstname = isset($data['firstname']) ? $data['firstname'] : null;
        $user->lastname = isset($data['lastname']) ? $data['lastname'] : null;
        $user->email = strtolower(trim($data['email']));
        $user->mobile =  $mobile;
        $user->mobile_code =  $mobile_code;
        $user->full_mobile =    $complete_phone;
        $user->password = Hash::make($data['password']);
        $user->username = $userName;
        $user->address = [
            'address' => isset($data['address']) ? $data['address'] : '',
            'city' => isset($data['city']) ? $data['city'] : '',
            'zip' => isset($data['zip_code']) ? $data['zip_code'] : '',
            'country' =>isset($data['country']) ? $data['country'] : '',
            'state' => isset($data['state']) ? $data['state'] : '',
        ];
        $user->status = 1;
        $user->email_verified       = $email_verified;
        $user->sms_verified         =  $sms_verified;
        $user->kyc_verified         =  ($basic_settings->kyc_verification == true) ? false : true;
        $user->registered_by        = $register_type??GlobalConst::EMAIL;
        $user->referral_id          = generate_unique_string('users','referral_id',8,'number');

        $user->save();
        if( $user && $basic_settings->kyc_verification == true){
            $create = [
                'user_id'       => $user->id,
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
                $error = ['error'=>[__('Something went wrong! Please try again.')]];
                return ApiHelpers::validation($error);
            }

           }
        $token = $user->createToken('user_token')->accessToken;

        $this->createUserWallets($user);
        if(isset($refer_system) &&  $refer_system->status == true){
            $this->createAsReferUserIfExists($request,$user);
            $this->createNewUserRegisterBonus($user);
            $this->assignReferralLevelToNewUser($user);
        }
        $this->createQr($user);
        $this->registerNotificationToAdminApi($user,'api',"USER");
        $data = ['token' => $token, 'user' => $user, ];
        $message =  ['success'=>[__('Registration Successful')]];
        return ApiHelpers::success($data,$message);

    }

    public function logout(){
        Auth::user()->token()->revoke();
        $message = ['success'=>[__('Logout Successfully!')]];
        return ApiHelpers::onlysuccess($message);

    }
    public function createQr($user){
		$user = $user;
	    $qrCode = $user->qrCode()->first();
        $in['user_id'] = $user->id;;
        $in['qr_code'] = $user->registered_by == GlobalConst::EMAIL ? $user->email : $user->full_mobile;
	    if(!$qrCode){
            UserQrCode::create($in);
	    }else{
            $qrCode->fill($in)->save();
        }
	    return $qrCode;
	}

}
