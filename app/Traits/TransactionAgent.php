<?php

namespace App\Traits;

use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Helpers\NotificationHelper;
use App\Http\Helpers\PushNotificationHelper;
use App\Models\Admin\BasicSettings;
use App\Models\Admin\ReferralSetting;
use App\Models\AgentNotification;
use App\Models\TemporaryData;
use App\Notifications\Admin\ActivityNotification;
use App\Notifications\User\AddMoney\ApprovedMail;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;
use App\Traits\User\RegisteredUsers;

trait TransactionAgent {
    use RegisteredUsers;
    public function createTransactionChildRecords($output,$status = PaymentGatewayConst::STATUSSUCCESS) {
        
        $trx_id = 'AM'.getTrxNum();
        $inserted_id = $this->insertRecordAgent($output,$trx_id,$status);
        $this->insertChargesAgent($output,$inserted_id);
        $this->adminNotification($trx_id,$output,$status);
        $this->insertDeviceAgent($output,$inserted_id);
        // $this->removeTempDataAgent($output);
         //send sms & email notifications

        $this->sendNotifications($output,$trx_id);

        if($this->requestIsApiUser()) {
            // logout user
            $api_user_login_guard = $this->output['api_login_guard'] ?? null;
            if($api_user_login_guard != null) {
                auth()->guard($api_user_login_guard)->logout();
            }
        }

    }

    public function insertRecordAgent($output,$trx_id,$status) {
        $trx_id = $trx_id;
        DB::beginTransaction();
        try{
            if($this->predefined_user) {
                $user = $this->predefined_user;
            }elseif(Auth::guard(userGuard()['guard'])->check()){
                $user = auth()->guard(userGuard()['guard'])->user();
            }

            $id = DB::table("transactions")->insertGetId([
                'agent_id'                      => $user->id,
                'agent_wallet_id'               => $output['wallet']->id,
                'payment_gateway_currency_id'   => $output['currency']->id,
                'type'                          =>  "ADD-MONEY",
                'trx_id'                        => $trx_id,
                'request_amount'                => $output['amount']->requested_amount,
                'payable'                       => $output['amount']->total_amount,
                'available_balance'             => $output['wallet']->balance + $output['amount']->requested_amount,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::TYPEADDMONEY," ")) . " With " . $output['gateway']->name,
               'details'                        => json_encode([
                                                        'amount'    => $output['amount']
                                                    ]),
                'status'                        => $status,
                'attribute'                      =>PaymentGatewayConst::SEND,
                'callback_ref'                  => $output['callback_ref'] ?? null,
                'created_at'                    => now(),
            ]);
            if($status === PaymentGatewayConst::STATUSSUCCESS) {
                $this->updateWalletBalanceAgent($output);
            }
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
        return $id;
    }
    public function updateWalletBalanceAgent($output) {
        $update_amount = $output['wallet']->balance + $output['amount']->requested_amount;
        $output['wallet']->update([
            'balance'   => $update_amount,
        ]);
    }
    public function insertChargesAgent($output,$id) {

        if($this->predefined_user) {
            $user = $this->predefined_user;
        }elseif(Auth::guard(userGuard()['guard'])->check()){
            $user = auth()->guard(userGuard()['guard'])->user();
        }
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $output['amount']->percent_charge,
                'fixed_charge'      => $output['amount']->fixed_charge,
                'total_charge'      => $output['amount']->total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         => __("Add Money"),
                'message'       => __("Your Wallet")." (".$output['wallet']->currency->code.")  ".__("balance  has been added")." ".$output['amount']->requested_amount.' '. $output['wallet']->currency->code,
                'time'          => Carbon::now()->diffForHumans(),
                'image'         => get_image($user->image,'agent-profile'),
            ];

            AgentNotification::create([
                'type'      => NotificationConst::BALANCE_ADDED,
                'agent_id'  =>   $user->id,
                'message'   => $notification_content,
            ]);
            //Push Notifications
            try{
                (new PushNotificationHelper())->prepare([$user->id],[
                    'title' => $notification_content['title'],
                    'desc'  => $notification_content['message'],
                    'user_type' => 'agent',
                ])->send();
            }catch(Exception $e) {}

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
    }
    public function insertDeviceAgent($output,$id) {
        $client_ip = request()->ip() ?? false;
        $location = geoip()->getLocation($client_ip);
        $agent = new Agent();

        // $mac = exec('getmac');
        // $mac = explode(" ",$mac);
        // $mac = array_shift($mac);
        $mac = "";

        DB::beginTransaction();
        try{
            DB::table("transaction_devices")->insert([
                'transaction_id'=> $id,
                'ip'            => $client_ip,
                'mac'           => $mac,
                'city'          => $location['city'] ?? "",
                'country'       => $location['country'] ?? "",
                'longitude'     => $location['lon'] ?? "",
                'latitude'      => $location['lat'] ?? "",
                'timezone'      => $location['timezone'] ?? "",
                'browser'       => $agent->browser() ?? "",
                'os'            => $agent->platform() ?? "",
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
    }
    public function removeTempDataAgent($output) {
        TemporaryData::where("identifier",$output['tempData']['identifier'])->delete();
    }
    //admin notification global(Agent & User)
    public function adminNotification($trx_id,$output,$status){
        if(!empty($this->predefined_user)) {
            $user = $this->predefined_user;
        }elseif(Auth::guard(userGuard()['guard'])->check()){
            $user = auth()->guard(userGuard()['guard'])->user();
        }
        $exchange_rate = " 1 ". $output['amount']->receiver_cur_code.' = '. get_amount($output['amount']->exchange_rate,$output['amount']->sender_cur_code);
        if($status == PaymentGatewayConst::STATUSSUCCESS){
            $status ="success";
        }elseif($status == PaymentGatewayConst::STATUSPENDING){
            $status ="Pending";
        }elseif($status == PaymentGatewayConst::STATUSHOLD){
            $status ="Hold";
        }elseif($status == PaymentGatewayConst::STATUSWAITING){
            $status ="Waiting";
        }elseif($status == PaymentGatewayConst::STATUSPROCESSING){
            $status ="Processing";
        }


        $notification_content = [
            //email notification
            'subject' =>__('Add Money'),
            'greeting' =>__("Add Money Via!")." ".$output['currency']->name,
            'email_content' =>__("web_trx_id")." : ".$trx_id."<br>".__("request Amount")." : ".get_amount($output['amount']->requested_amount,$output['amount']->receiver_cur_code)."<br>".__("Exchange Rate")." : ". $exchange_rate."<br>".__("Fees & Charges")." : ". get_amount($output['amount']->total_charge,$output['amount']->sender_cur_code)."<br>".__("Total Payable Amount")." : ".get_amount($output['amount']->total_amount,$output['amount']->receiver_cur_code)."<br>".__("Status")." : ".__($status),
            //push notification
            'push_title' =>  __('Add Money'),
            'push_content' => __('web_trx_id')." ".$trx_id." ". __('Add Money').' '.$output['amount']->requested_amount.' '.$output['amount']->receiver_cur_code.' '.__('By').' '.$output['currency']->name.' ('.$user->username.')',

            //admin db notification
            'notification_type' =>  NotificationConst::ADD_MONEY,
            'trx_id' =>  $trx_id,
            'admin_db_title' =>  'Add Money',
            'admin_db_message' => 'Add Money'.' '.$output['amount']->requested_amount.' '.$output['amount']->receiver_cur_code.' '.'By'.' '. $output['currency']->name.' ('.$user->username.')'
        ];


        try{
            //notification
            (new NotificationHelper())->admin(['admin.add.money.index','admin.add.money.pending','admin.add.money.complete','admin.add.money.canceled','admin.add.money.details','admin.add.money.approved','admin.add.money.rejected','admin.add.money.export.data'])
                                    ->mail(ActivityNotification::class, [
                                        'subject'   => $notification_content['subject'],
                                        'greeting'  => $notification_content['greeting'],
                                        'content'   => $notification_content['email_content'],
                                    ])
                                    ->push([
                                        'user_type' => "admin",
                                        'title' => $notification_content['push_title'],
                                        'desc'  => $notification_content['push_content'],
                                    ])
                                    ->adminDbContent([
                                        'type' => $notification_content['notification_type'],
                                        'title' => $notification_content['admin_db_title'],
                                        'message'  => $notification_content['admin_db_message'],
                                    ])
                                    ->send();


        }catch(Exception $e) {}

    }
    //agent & user notifications
    public function sendNotifications($output,$trx_id){

        $basic_setting = BasicSettings::first();
        if($this->predefined_user) {
            $user = $this->predefined_user;
        }elseif(Auth::guard(userGuard()['guard'])->check()){
            $user = auth()->guard(userGuard()['guard'])->user();
        }
        if(userGuard()['type'] == "USER"){
            $email_send_status  = $basic_setting->email_notification;
            $sms_send_status    = $basic_setting->sms_notification;
            //set referral bonus

            $refer_system = ReferralSetting::first();
            if(isset($refer_system) &&  $refer_system->status == true){
                $this->referUserLevelUpInspection($user);
            }

        }elseif(userGuard()['type'] == "AGENT"){
            $email_send_status  = $basic_setting->agent_email_notification;
            $sms_send_status    = $basic_setting->agent_sms_notification;
        }else{
            $email_send_status  = $basic_setting->email_notification;
            $sms_send_status    = $basic_setting->sms_notification;
        }

        //email notification
        try{
            if( $email_send_status == true){
                $user->notify(new ApprovedMail($user,$output,$trx_id));
            }
        }catch(Exception $e){}

        //admin notifications
        if($sms_send_status == true){
            try{
                sendSms($user, 'ADD_MONEY_COMPLETE', [
                    'amount' =>  getAmount($output['amount']->requested_amount,2),
                    'currency' => @$output['amount']->receiver_cur_code,
                    'gateway_name' => @$output['currency']['name'],
                ]);
            }catch(Exception $e){}
        }

    }

}
