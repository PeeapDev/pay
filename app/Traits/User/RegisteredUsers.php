<?php

namespace App\Traits\User;

use App\Constants\GlobalConst;
use App\Constants\PaymentGatewayConst;
use App\Models\Admin\Currency;
use App\Models\Admin\ReferralSetting;
use App\Models\ReferralLevelPackage;
use App\Models\ReferredUser;
use App\Models\User;
use App\Models\UserWallet;
use App\Notifications\User\RegistrationBonusNotification;
use App\Providers\Admin\BasicSettingsProvider;
use App\Providers\Admin\CurrencyProvider;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;

trait RegisteredUsers {
    protected function createUserWallets($user) {
        $currencies = Currency::active()->roleHasOne()->pluck("id")->toArray();
        $wallets = [];
        foreach($currencies as $currency_id) {
            $wallets[] = [
                'user_id'       => $user->id,
                'currency_id'   => $currency_id,
                'balance'       => 0,
                'status'        => true,
                'created_at'    => now(),
            ];
        }

        try{
            UserWallet::insert($wallets);
        }catch(Exception $e) {
            // handle error
            $this->guard()->logout();
            $user->delete();
            return $this->breakAuthentication(__("Failed to create wallet! Please try again"));
        }
    }


    protected function breakAuthentication($error) {
        return back()->with(['error' => [$error]]);
    }
    protected function createAsReferUserIfExists(Request $request, $user) {
        if($request->has('refer') && $request->refer != null && User::where('referral_id',trim($request->refer))->exists()) {
            $refer_user = User::where('referral_id',trim($request->refer))->first();
            $refer_user_id = $refer_user->id; // who refer this new user
            try{
                ReferredUser::create([
                    'refer_user_id'     => $refer_user_id,
                    'new_user_id'       => $user->id,
                    'created_at'        => now(),
                ]);

                $this->referUserActions($refer_user); // who refer the new user
            }catch(Exception $e) {
                throw new Exception($e);
            }
        }
    }

    protected function createNewUserRegisterBonus($user) {
        $referral_settings = ReferralSetting::first();
        if($referral_settings && $referral_settings->status == GlobalConst::ACTIVE && $referral_settings->bonus > 0) {

            // need to add bonus in user wallet
            $default_currency = CurrencyProvider::default();
            $system_super_admin = get_super_admin();

            $user_wallet = $user->wallets()->whereHas('currency',function($query) use ($default_currency) {
                $query->where('code',$default_currency->code);
            })->first();

            // create bonus transaction
            DB::beginTransaction();
            try{
                $transaction_id =  DB::table('transactions')->insertGetId([
                    'type'              => PaymentGatewayConst::TYPEBONUS,
                    'trx_id'            => generate_unique_string('transactions','trx_id',16),
                    'user_id'           => $user->id,
                    'user_wallet_id'    => $user_wallet->id,
                    'request_amount'    => $referral_settings->bonus,
                    'payable'           => 0,
                    'available_balance' => $user_wallet->balance + $referral_settings->bonus,
                    'details'           => json_encode([
                                            'user_type'         => GlobalConst::USER,
                                            'request_currency'  =>  $default_currency->code,
                                            'exchange_rate'     =>  1,
                                            'receive_amount'    =>   $referral_settings->bonus,
                                            'sender_type'       =>   GlobalConst::ADMIN,
                                            'sender_id'         =>   $system_super_admin->id,
                                            'payment_currency'  =>   $default_currency->code,
                                        ]),
                    'remark'            => Lang::get("Registration Bonus"),
                    'attribute'         => PaymentGatewayConst::RECEIVED,
                    'status'            => PaymentGatewayConst::STATUSSUCCESS,
                    'created_at'        => now(),
                ]);

                //transaction charges create
                DB::table('transaction_charges')->insert([
                    'transaction_id'    => $transaction_id,
                    'percent_charge'    => 0,
                    'fixed_charge'      => 0,
                    'total_charge'      => 0,
                    'created_at'        => now(),
                ]);

                $wallet_type = [
                    GlobalConst::CURRENT_BALANCE    => 'balance',
                ];

                // Update user wallet balance
                DB::table($user_wallet->getTable())->where('id',$user_wallet->id)->increment($wallet_type[$referral_settings->wallet_type],$referral_settings->bonus);

                // Send mail if enable
                if($referral_settings->mail == GlobalConst::ACTIVE) {
                    try{
                        $user->notify((new RegistrationBonusNotification([
                            'user_name'         => $user->fullname,
                            'system_name'       => BasicSettingsProvider::get()->site_name,
                            'bonus_amount'      => $referral_settings->bonus,
                            'currency_code'     => $default_currency->code,
                        ]))->delay(20));
                    }catch(Exception $e){}
                }
                // if($referral_settings->sms == GlobalConst::ACTIVE) {
                //     try{
                //         sendSms($user,'REGISTRATION_BONUS',[
                //             'user_name'     => $user->fullname,
                //             'amount'        => get_amount($referral_settings->bonus,$default_currency->code),
                //             'time'          => now()->format('Y-m-d h:i:s A')
                //         ]);
                //     }catch(Exception $e){}
                // }
                DB::commit();
            }catch(Exception $e) {
                DB::rollBack();
                throw new Exception($e->getMessage());
            }
        }
    }

    protected function assignReferralLevelToNewUser($user) {
        $default_referral_level = ReferralLevelPackage::default()->first();
        if($default_referral_level && $default_referral_level->refer_user <= 0 && $default_referral_level->deposit_amount <= 0) { // if required refer user 0 and required deposit amount 0
            $this->assignReferralLevelToUser($user, $default_referral_level);
        }
    }

    protected function assignReferralLevelToUser($user, $referral_level) {
        if($referral_level) {
            DB::beginTransaction();
            try{

                DB::table($user->getTable())->where('id',$user->id)->update([
                    'current_referral_level_id' => $referral_level->id,
                ]);

                // Update User Referral Level Earn Table
                if(!DB::table('user_earn_referral_levels')->where('user_id',$user->id)->where('referral_level_package_id', $referral_level->id)->exists()) {
                    DB::table('user_earn_referral_levels')->insert([
                        'user_id'                   => $user->id,
                        'referral_level_package_id' => $referral_level->id,
                        'created_at'                => now(),
                    ]);
                }

                DB::commit();
            }catch(Exception $e) {
                DB::rollBack();
                throw new Exception($e->getMessage());
            }
        }
    }

    protected function referUserActions($refer_user) // who refer the new user
    {
        // check refer user level
        if($refer_user->referLevel) { // if have any level create refer bonus
            // add refer bonus if have
            $this->createReferBonus($refer_user); // create refer bonus as per level commission
        }

        // Refer user level up checking
        $this->referUserLevelUpInspection($refer_user);
    }

    protected function createReferBonus($user) {
        $refer_level = $user->referLevel;
        $refer_user = $user;

        $system_super_admin = get_super_admin();
        $default_currency = CurrencyProvider::default();

        $refer_user_wallet = $refer_user->wallets()->whereHas('currency',function($query) use ($default_currency) {
            $query->where('code',$default_currency->code);
        })->first();

        DB::beginTransaction();
        try{
            $transaction_id = DB::table('transactions')->insertGetId([
                'type'              => PaymentGatewayConst::TYPEREFERBONUS,
                'trx_id'            => generate_unique_string('transactions','trx_id',16),
                'user_id'           => $refer_user->id,
                'user_wallet_id'    => $refer_user_wallet->id,
                'request_amount'    => $refer_level->commission,
                'payable'           => $refer_level->commission,
                'available_balance' => $refer_user_wallet->balance + $refer_level->commission,
                'details'           =>  json_encode([
                                            'user_type'         => GlobalConst::USER,
                                            'request_currency'  =>  $default_currency->code,
                                            'exchange_rate'     =>  1,
                                            'receive_amount'    =>  $refer_level->commission,
                                            'sender_type'       =>  GlobalConst::ADMIN,
                                            'sender_id'         =>   $system_super_admin->id,
                                            'payment_currency'  =>  $default_currency->code,
                                        ]),
                'status'            => PaymentGatewayConst::STATUSSUCCESS,
                'attribute'         => PaymentGatewayConst::RECEIVED,
                'created_at'        => now(),
            ]);

            //transaction charges create
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $transaction_id,
                'percent_charge'    => 0,
                'fixed_charge'      => 0,
                'total_charge'      => 0,
                'created_at'        => now(),
            ]);

            // Update user wallet balance
            DB::table($refer_user_wallet->getTable())->where('id',$refer_user_wallet->id)->increment('balance',$refer_level->commission);

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    protected function referUserLevelUpInspection($refer_user) {
        $next_refer_level = $refer_user->nextReferLevel();
        if($next_refer_level == false) return false; // already refer use earn top level or level does not exists from system

        if($this->ifReferUserEligibleToNextLevel($refer_user, $next_refer_level)) { // if refer user filled next level requirements
            // need to upgrade next level
            $this->assignReferralLevelToUser($refer_user,$next_refer_level);
        }
    }

    protected function ifReferUserEligibleToNextLevel($refer_user, $next_level) {
        $current_refer_users            = $refer_user->referUsers->count();
        $current_refer_user_investment  = totalDeposit($refer_user->depositAmount);
        if($current_refer_users >= $next_level->refer_user && $current_refer_user_investment >= $next_level->deposit_amount) {
            return true;
        }
        return false;
    }
}
