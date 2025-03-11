<?php

namespace App\Http\Controllers\Agent\Auth;

use App\Constants\ExtensionConst;
use App\Http\Controllers\Controller;
use App\Constants\GlobalConst;
use App\Models\Admin\SetupKyc;
use App\Models\Agent;
use App\Models\AgentAuthorization;
use App\Providers\Admin\BasicSettingsProvider;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Auth\Events\Registered;
use App\Notifications\User\Auth\SendVerifyCode;
use App\Traits\Agent\RegisteredUsers;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Traits\ControlDynamicInputFields;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
use App\Traits\AdminNotifications\AuthNotifications;
use App\Http\Helpers\Response;
use App\Models\Merchants\Merchant;
use App\Models\User;
use App\Providers\Admin\ExtensionProvider;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers, RegisteredUsers, ControlDynamicInputFields,AuthNotifications;

    protected $basic_settings;

    public function __construct()
    {
        $this->basic_settings = BasicSettingsProvider::get();
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm() {
        $client_ip = request()->ip() ?? false;
        $user_country = geoip()->getLocation($client_ip)['country'] ?? "";

        $page_title = __("Agent Registration");
        return view('agent.auth.register',compact(
            'page_title',
            'user_country',
        ));
    }
    //========================before registration======================================

    public function sendVerifyCode(Request $request){
        $basic_settings = $this->basic_settings;
        if($basic_settings->agree_policy){
            $agree = 'required';
        }else{
            $agree = '';
        }
        if($request->register_type == global_const()::PHONE){
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
            'g-recaptcha-response'  => $captcha_rules

        ])->validate();


        if($validated['register_type'] == GlobalConst::PHONE){
            $code = generate_random_code();

            $country = select_country($validated['mobile_code']);
            $mobile_code = remove_special_char($country->mobile_code);
            $mobile = $mobile_code == '880' ? (int)$validated['credentials'] :  $validated['credentials'];
            $full_mobile = $mobile_code.$mobile;

            $sms_verify_status = ($basic_settings->agent_sms_verification == true) ? false : true;

            $exist              = User::where('full_mobile', $full_mobile)->first();
            $exists_agent       = Agent::where('full_mobile', $full_mobile)->first();
            $exists_merchant    = Merchant::where('full_mobile', $full_mobile)->first();

            if( $exist || $exists_agent || $exists_merchant) return back()->with(['error' => [__("User already  exists, please try with another phone or email address.")]]);
            $data = [
                'agent_id'       => 0,
                'phone'         => $full_mobile,
                'code'          => $code,
                'token'         => generate_unique_string("agent_authorizations","token",200),
                'created_at'    => now(),
            ];

            DB::beginTransaction();
            try{
                if($basic_settings->agent_sms_verification == false){
                    Session::put('register_data',[
                        'credentials'   =>  $mobile,
                        'mobile_code'   => $mobile_code,
                        'country_name'  => $country->name,
                        'register_type' => $validated['register_type'],
                        'sms_verified'  => $sms_verify_status,
                    ]);
                    return redirect()->route('agent.register.kyc');
                }
                DB::table("agent_authorizations")->insert($data);
                Session::put('register_data',[
                    'credentials'   =>  $mobile,
                    'mobile_code'   => $mobile_code,
                    'country_name'  => $country->name,
                    'register_type' => $validated['register_type'],
                    'sms_verified'  => $sms_verify_status,
                ]);
                if($basic_settings->agent_sms_notification == true && $basic_settings->agent_sms_verification == true){
                    try{
                        sendSmsNotAuthUser($full_mobile, 'SVER_CODE', [
                            'code' => $code
                        ]);
                    }catch(Exception $e) {}
                }
                DB::commit();
            }catch(Exception $e) {
                DB::rollBack();
                return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
            };
            return redirect()->route('agent.sms.verify',$data['token'])->with(['success' => [__('Verification code sended to your email address.')]]);
        }else{
            $exist              = User::where('email',$validated['credentials'])->first();
            $exists_agent       = Agent::where('email',$validated['credentials'])->first();
            $exists_merchant    = Merchant::where('email',$validated['credentials'])->first();
            if( $exist || $exists_agent || $exists_merchant) return  back()->with(['error' => [__("User already  exists, please try with another phone or email address.")]]);

            $code = generate_random_code();
            $email_verify_status = ($basic_settings->agent_email_verification == true) ? false : true;

            $data = [
                'agent_id'       =>  0,
                'email'         => $validated['credentials'],
                'code'          => $code,
                'token'         => generate_unique_string("agent_authorizations","token",200),
                'created_at'    => now(),
            ];
            DB::beginTransaction();
            try{
                if($basic_settings->agent_email_verification == false){
                    Session::put('register_data',[
                        'credentials'   => $validated['credentials'],
                        'register_type' => $validated['register_type'],
                        'email_verified'  => $email_verify_status,
                    ]);

                    return redirect()->route('agent.register.kyc');
                }
                DB::table("agent_authorizations")->insert($data);
                Session::put('register_data',[
                    'credentials'   => $validated['credentials'],
                    'register_type' => $validated['register_type'],
                    'email_verified'  => $email_verify_status,
                ]);
                if($basic_settings->agent_email_notification == true && $basic_settings->agent_email_verification == true){
                    try{
                        Notification::route("mail",$validated['credentials'])->notify(new SendVerifyCode($validated['credentials'], $code));
                    }catch(Exception $e){}
                }
                DB::commit();
            }catch(Exception $e) {
                DB::rollBack();
                return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
            };

            return redirect()->route('agent.email.verify',$data['token'])->with(['success' => [__('Verification code sended to your email address.')]]);
        }
    }

    public function verifyCode(Request $request,$token){
        $request->merge(['token' => $token]);
        $request->validate([
            'token'     => "required|string|exists:agent_authorizations,token",
            'code'      => "required|array",
            'code.*'    => "required|numeric",
        ]);
        $code = $request->code;
        $code = implode("",$code);
        $otp_exp_sec = BasicSettingsProvider::get()->agent_otp_exp_seconds ?? GlobalConst::DEFAULT_TOKEN_EXP_SEC;
        $auth_column = AgentAuthorization::where("token",$request->token)->where("code",$code)->first();
        if(!$auth_column){
            return back()->with(['error' => [__('The verification code does not match')]]);
        }
        if($auth_column->created_at->addSeconds($otp_exp_sec) < now()) {
            $auth_column->delete();
            return redirect()->route('agent.register')->with(['error' => [__('Session expired. Please try again')]]);
        }
        try{
            $auth_column->delete();
        }catch(Exception $e) {
            return redirect()->route('agent.register')->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return redirect()->route("agent.register.kyc")->with(['success' => [__('Otp successfully verified')]]);
    }
    public function resendCode(Request $request){
        $email = session()->get('register_data.credentials');
        $resend = AgentAuthorization::where("email",$email)->first();
        if( $resend){
            if(Carbon::now() <= $resend->created_at->addMinutes(GlobalConst::USER_VERIFY_RESEND_TIME_MINUTE)) {
                throw ValidationException::withMessages([
                    'code'      => __('You can resend the verification code after').' '.Carbon::now()->diffInSeconds($resend->created_at->addMinutes(GlobalConst::USER_PASS_RESEND_TIME_MINUTE)). ' '. __('seconds'),
                ]);
            }
        }

        $code = generate_random_code();
        $data = [
            'agent_id'       =>  0,
            'email'         => $email,
            'code'          => $code,
            'token'         => generate_unique_string("agent_authorizations","token",200),
            'created_at'    => now(),
        ];
        DB::beginTransaction();
        try{
            $oldToken = AgentAuthorization::where("email",$email)->get();
            if($oldToken){
                foreach($oldToken as $token){
                    $token->delete();
                }
            }
            DB::table("agent_authorizations")->insert($data);
            if($this->basic_settings->agent_email_notification == true ){
                try{
                    Notification::route("mail",$email)->notify(new SendVerifyCode($email, $code));
                }catch(Exception $e){}
            }
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
        return redirect()->route('agent.email.verify',$data['token'])->with(['success' => [__('Verification code resend success')]]);
    }

     /**
     * Method for sms verify code
     * @param $token
     */
    public function smsVerifyCode(Request $request,$token){
        $request->merge(['token' => $token]);
        $request->validate([
            'token'     => "required|string|exists:agent_authorizations,token",
            'code'      => "required|array",
            'code.*'    => "required|numeric",
        ]);
        $code = $request->code;
        $code = implode("",$code);
        $otp_exp_sec = BasicSettingsProvider::get()->otp_exp_seconds ?? GlobalConst::DEFAULT_TOKEN_EXP_SEC;
        $auth_column = AgentAuthorization::where("token",$request->token)->where("code",$code)->first();
        if(!$auth_column){
            return back()->with(['error' => [__('The verification code does not match')]]);
        }
        if($auth_column->created_at->addSeconds($otp_exp_sec) < now()) {
            $auth_column->delete();
            return redirect()->route('agent.register')->with(['error' => [__('Session expired. Please try again')]]);
        }
        try{
            $auth_column->delete();
        }catch(Exception $e) {
            return redirect()->route('agent.register')->with(['error' => [__('Something went wrong! Please try again.')]]);
        }
        return redirect()->route("agent.register.kyc")->with(['success' => [__('Otp successfully verified')]]);
    }
     /**
     * Method for sms resend code
     */
    public function smsResendCode(){
        $mobile_code = remove_special_char(session()->get('register_data.mobile_code'));
        $phone = $mobile_code.session()->get('register_data.credentials');
        $resend = AgentAuthorization::where("phone",$phone)->first();

        if( $resend){
            if(Carbon::now() <= $resend->created_at->addMinutes(GlobalConst::USER_VERIFY_RESEND_TIME_MINUTE)) {
                throw ValidationException::withMessages([
                    'code'      => __('You can resend the verification code after').' '.Carbon::now()->diffInSeconds($resend->created_at->addMinutes(GlobalConst::USER_PASS_RESEND_TIME_MINUTE)). ' '. __('seconds'),
                ]);
            }
        }

        $code = generate_random_code();
        $data = [
            'agent_id'       =>  0,
            'phone'         => $phone,
            'code'          => $code,
            'token'         => generate_unique_string("agent_authorizations","token",200),
            'created_at'    => now(),
        ];
        DB::beginTransaction();
        try{
            $oldToken = AgentAuthorization::where("phone",$phone)->get();
            if($oldToken){
                foreach($oldToken as $token){
                    $token->delete();
                }
            }
            DB::table("agent_authorizations")->insert($data);
            if($this->basic_settings->agent_sms_notification == true ){
                try{
                    sendSmsNotAuthUser($phone,'SVER_CODE', [
                        'code' => $code
                    ]);
                }catch(Exception $e) {}
            }
            DB::commit();

        }catch(Exception $e) {
            DB::rollBack();
            return back()->with(['error' => [__('Something went wrong! Please try again.')]]);
        }
        return redirect()->route('agent.sms.verify',$data['token'])->with(['success' => [__('Verification code resend success')]]);
    }
    public function registerKyc(Request $request){
        $basic_settings   = $this->basic_settings;
        $country_name      = session()->get('register_data.country_name')??null;
        $mobile_code      = session()->get('register_data.mobile_code')??null;
        $credentials      = session()->get('register_data.credentials');
        $register_type    = session()->get('register_data.register_type');
        if($credentials == null){
            return redirect()->route('agent.register');
        }
        $kyc_fields =[];
        if($basic_settings->agent_kyc_verification == true){
            $user_kyc = SetupKyc::agentKyc()->first();
            if($user_kyc != null){
                $kyc_data = $user_kyc->fields;
                $kyc_fields = [];
                if($kyc_data) {
                    $kyc_fields = array_reverse($kyc_data);
                }
            }else{
                $kyc_fields =[];
            }
        }
        $page_title = __("Agent Registration KYC");
        return view('agent.auth.register-kyc',compact(
            'page_title',
            'mobile_code',
            'credentials',
            'register_type',
            'kyc_fields',
            'country_name'

        ));
    }
    //========================before registration======================================

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $basic_settings             = $this->basic_settings;
        $validated = $this->validator($request->all())->validate();
        if($basic_settings->agent_kyc_verification == true){
            $user_kyc_fields = SetupKyc::agentKyc()->first()->fields ?? [];
            $validation_rules = $this->generateValidationRules($user_kyc_fields);
            $kyc_validated = Validator::make($request->all(),$validation_rules)->validate();
            $get_values = $this->registerPlaceValueWithFields($user_kyc_fields,$kyc_validated);
        }

        $validated['mobile_code']           = remove_speacial_char($validated['phone_code']);
        $validated['mobile']                = get_mobile_number($validated['mobile_code'],$validated['phone']);
        $complete_phone                     = $validated['mobile'] == null ? null : $validated['mobile_code'] . $validated['mobile'];

        if(User::where('full_mobile',$complete_phone)->orWhere('email',$validated['email'])->exists()) {
            throw ValidationException::withMessages([
                'phone'     => __('The phone number or email address you have provided is already in use.'),
            ]);
        }
        if(Agent::where('full_mobile',$complete_phone)->orWhere('email',$validated['email'])->exists()) {
            throw ValidationException::withMessages([
                'phone'     => __('The phone number or email address you have provided is already in use.'),
            ]);
        }
        if(Merchant::where('full_mobile',$complete_phone)->orWhere('email',$validated['email'])->exists()) {
            throw ValidationException::withMessages([
                'phone'     => __('The phone number or email address you have provided is already in use.'),
            ]);
        }

        $userName = make_username($validated['firstname'],$validated['lastname'],'agents');
        $check_user_name = Agent::where('username',$userName)->first();
        if($check_user_name){
            $userName = $userName.'-'.rand(123,456);
        }

        //check register type
        $register_data      = session()->get('register_data');

        if($register_data['register_type'] == GlobalConst::PHONE){
            if($register_data['sms_verified'] == true && $basic_settings->agent_sms_verification == false){
                $sms_verified = true;
            }elseif($register_data['sms_verified'] == false && $basic_settings->agent_sms_verification == true){
                $sms_verified = true;
            }else{
                $sms_verified = false;
            }
        }elseif($basic_settings->agent_sms_verification == false){
            $sms_verified = true;
        }else{
            $sms_verified = false;
        }

        if($register_data['register_type'] == GlobalConst::EMAIL){
            if($register_data['email_verified'] == true && $basic_settings->agent_email_verification == false){
                $email_verified = true;
            }elseif($register_data['email_verified'] == false && $basic_settings->agent_email_verification == true){
                $email_verified = true;
            }else{
                $email_verified = false;
            }
        }elseif($basic_settings->agent_email_verification == false){
            $email_verified = true;
        }else{
            $email_verified = false;
        }




        $validated['full_mobile']       = $complete_phone;
        $validated = Arr::except($validated,['agree','phone_code','phone']);
        $validated['email_verified']    = $email_verified;
        $validated['sms_verified']      = $sms_verified;
        $validated['kyc_verified']      = ($basic_settings->agent_kyc_verification == true) ? false : true;
        $validated['password']          = Hash::make($validated['password']);
        $validated['username']          =  $userName;
        $validated['address']           = [
                                            'country' => $validated['country'],
                                            'city' => $validated['city'],
                                            'zip' => $validated['zip_code'],
                                            'state' => '',
                                            'address' => '',
                                        ];
        $validated['registered_by']     = $register_data['register_type']??GlobalConst::EMAIL;
        $data = event(new Registered($user = $this->create($validated)));
        if( $data  && $basic_settings->agent_kyc_verification == true){
        $create = [
            'agent_id'       => $user->id,
            'data'          => json_encode($get_values),
            'created_at'    => now(),
        ];

        DB::beginTransaction();
        try{
            DB::table('agent_kyc_data')->updateOrInsert(["agent_id" => $user->id],$create);
            $user->update([
                'kyc_verified'  => GlobalConst::PENDING,
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $user->update([
                'kyc_verified'  => GlobalConst::DEFAULT,
            ]);

            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }


       }
       $request->session()->forget('register_data');
        $this->guard()->login($user);

        return $this->registered($request, $user);
    }
    protected function guard()
    {
        return Auth::guard('agent');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function validator(array $data) {
        $basic_settings = $this->basic_settings;
        $passowrd_rule = "required|string|min:6|confirmed";
        if($basic_settings->agent_secure_password) {
            $passowrd_rule = ["required","confirmed",Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised()];
        }
        if($basic_settings->agent_agree_policy){
            $agree = 'required';
        }else{
            $agree = '';
        }
        if( $basic_settings->agent_email_verification){
            $email_field ='required|string|email|max:150|unique:agents,email';
        }else{
            $email_field ='nullable';
        }
        if( $basic_settings->agent_sms_verification){
            $mobile_code_field  = 'required|string|max:10';
            $mobile_field ='required|string|max:20|unique:agents,mobile';
        }else{
            $mobile_code_field  ='nullable';
            $mobile_field       ='nullable';
        }


        return Validator::make($data,[
            'firstname'     => 'required|string|max:60',
            'lastname'      => 'required|string|max:60',
            'store_name'    => 'required|string|max:200',
            'email'         => $email_field,
            'password'      => $passowrd_rule,
            'country'       => 'required|string|max:150',
            'city'          => 'required|string|max:150',
            'phone_code'    => $mobile_code_field,
            'phone'         => $mobile_field,
            'zip_code'         => 'required|string|max:8',
            'agree'         =>  $agree,
        ]);
    }


    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return Agent::create($data);
    }


    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        $user->createQr();
        $this->createUserWallets($user);
        $this->registerNotificationToAdmin($user);
        return redirect()->intended(route('agent.dashboard'));
    }
}
