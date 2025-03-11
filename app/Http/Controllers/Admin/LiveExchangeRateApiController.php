<?php

namespace App\Http\Controllers\Admin;

use App\Constants\GlobalConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\CurrencyLayer;
use App\Http\Helpers\Response;
use App\Models\Admin\Currency;
use App\Models\Admin\PaymentGatewayCurrency;
use App\Models\LiveExchangeRateApiSetting;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LiveExchangeRateApiController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title = __("Setup Live Exchange Rate API");
        $providers = LiveExchangeRateApiSetting::latest()->paginate(20);

        return view('admin.sections.exchange-rate.index',compact(
            'page_title',
            'providers',
        ));
    }
    /**
     * Function for edit live exchange api provider
     * @param  \Illuminate\Http\Request  $request
     */
    public function edit($slug)
    {
        $page_title =__("Edit Live Exchange Rate API");
        $provider = LiveExchangeRateApiSetting::where('slug',$slug)->firstOrfail();
        if($provider->slug == GlobalConst::CURRENCY_LAYER){
            $get_supported_countries  = (new CurrencyLayer())->apiCurrencyList();
            if(isset( $get_supported_countries) && isset( $get_supported_countries['status']) &&  $get_supported_countries['status'] == true){
                $in['access_key']           = $provider->value->access_key??"";
                $in['base_url']             = $provider->value->base_url??"";
                $in['supported_currencies'] = array_keys($get_supported_countries['data']);
                $provider->update([
                    'value' => $in
                ]);
            }
        }
        return view('admin.sections.exchange-rate.edit',compact(
            'page_title',
            'provider',
        ));
    }
    /**
     * Function for update live exchange api provider
     * @param  \Illuminate\Http\Request  $request
     */
    public function update(Request $request,$slug){

        $validator = Validator::make($request->all(), [
            'access_key'        => 'required|string',
            'base_url'          => 'required|url',
            'multiply_by'       => 'required|numeric|gt:0',
        ]);
        if($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $validated = $validator->validate();

        try{
            $provider = LiveExchangeRateApiSetting::where('slug',$slug)->firstOrfail();

            $credentials = array_filter($request->except('_token','env','_method','multiply_by'));
            $data['value']          =  $credentials;
            $data['multiply_by']    =  $validated['multiply_by'];

            if($provider->slug == GlobalConst::CURRENCY_LAYER){
                $get_supported_countries  = (new CurrencyLayer())->apiCurrencyList();
                if(isset( $get_supported_countries) && isset( $get_supported_countries['status']) &&  $get_supported_countries['status'] == true){
                    $data['value']['supported_currencies'] = array_keys($get_supported_countries['data'])??[];
                }
            }
            $provider->fill($data)->save();
        }catch(Exception $e){
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
        return back()->with(['success' => [__("The Exchange Rate API Has Been Updated.")]]);
    }

    /**
     * Function for update live exchange api status
     * @param  \Illuminate\Http\Request  $request
     */
    public function statusUpdate(Request $request) {

        $validator = Validator::make($request->all(),[
            'data_target'       => 'required|numeric',
            'status'            => 'required|integer',
        ]);

        if($validator->stopOnFirstFailure()->fails()) {
            return Response::error($validator->errors());
        }

        $validated = $validator->validate();

        $status = [
            0 => true,
            1 => false,
        ];

        // find target Item
        $provider = LiveExchangeRateApiSetting::find($validated['data_target']);
        if(!$provider) {
            $error = ['error' => [__("Invalid provider or provider not found!")]];
            return Response::error($error,null,404);
        }
        try{
            $provider->update([
                'status'        => $status[$validated['status']],
            ]);
        }catch(Exception $e) {
            $error = ['error' => [__("Something went wrong! Please try again.")]];
            return Response::error($error,null,500);
        }

        $success = ['success' => [__("Provider status updated successfully!")]];
        return Response::success($success);

    }
    /**
     * Function for search live exchange api provider
     * @param  \Illuminate\Http\Request  $request
     */
    public function search(Request $request) {
        $validator = Validator::make($request->all(),[
            'text'  => 'required|string',
        ]);
        if($validator->fails()) {
            $error = ['error' => $validator->errors()];
            return Response::error($error,null,400);
        }
        $validated = $validator->validate();
        $providers = LiveExchangeRateApiSetting::search($validated['text'])->select()->limit(20)->get();
        return view('admin.components.data-table.exchange-rate-table',compact(
            'providers',
        ));
    }

    public function modulePermission(Request $request) {

        if($request->input_name == 'payment_gateway_module'){
            $field   = 'payment_gateway_module';

        }elseif($request->input_name == 'currency_module'){
            $field   = 'currency_module';
        }

        $validator = Validator::make($request->all(),[
            'status'                    => 'required|boolean',
            'data_target'               => 'required|string',
        ]);
        if ($validator->stopOnFirstFailure()->fails()) {
            $error = ['error' => $validator->errors()];
            return Response::error($error,null,400);
        }
        $validated = $validator->safe()->all();
        $page_slug = $validated['data_target'];

        $page = LiveExchangeRateApiSetting::where('slug',$page_slug)->first();
        if(!$page) {
            $error = ['error' => [__("Module not found!")]];
            return Response::error($error,null,404);
        }
        try{
            $page->update([
                $field => ($validated['status'] == true) ? false : true,
            ]);
        }catch(Exception $e) {
            $error = ['error' => [__("Something went wrong! Please try again.")]];
            return Response::error($error,null,500);
        }

        $success = ['success' => [__("Module status updated successfully!")]];
        return Response::success($success,null,200);
    }
    public function sendRequestApi(Request $request){

        try{
            $api_rates = (new CurrencyLayer())->getLiveExchangeRates();
            $api_rates['data'][get_default_currency_code()] = get_amount(1,null,get_wallet_precision());
            
            if(isset($api_rates) && $api_rates['status'] == false){
                return back()->with(['error' => [$api_rates['message'] ??__("Something went wrong! Please try again.")]]);
            }
            $api_rates =  $api_rates['data'];
            $provider = LiveExchangeRateApiSetting::where('slug',GlobalConst::CURRENCY_LAYER)->first();

            // For Setup Currency Rate Update
            if( $provider->currency_module == 1){
                $currencies = Currency::active()->get();
                foreach ($currencies as $currency) {
                    if (array_key_exists($currency->code, $api_rates)) {
                        $currency->rate = $api_rates[$currency->code];
                        $currency->save();
                    }
                }
            }

            //For Gateway Currency Rate Update
            if( $provider->payment_gateway_module == 1){
                $payment_gateways_currencies = PaymentGatewayCurrency::whereHas('gateway', function ($gateway) {
                    $gateway->where('status', 1);
                })->get();
                foreach ($payment_gateways_currencies as $currency) {
                    if (array_key_exists($currency->currency_code, $api_rates)) {
                        $currency->rate = $api_rates[$currency->currency_code];
                        $currency->save();
                    }
                }
            }
            return back()->with(['success' => [__("Currency Rate Updated By Currency Layer.")]]);
        }catch(Exception $e){
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
    }

}
