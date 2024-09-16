<?php

namespace Modules\CryptoExchange\Http\Controllers\Admin;

use Modules\CryptoExchange\Datatables\ExchangeDirectionsDataTable;
use Modules\CryptoExchange\Http\Requests\StoreDirectionRequest;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Helpers\Common;
use Modules\CryptoExchange\Entities\{
    ExchangeDirection,
    CryptoExchange
};

class ExchangeDirectionController extends Controller
{
    public $helper;
    
    public function __construct(Common $helper)
    {
        $this->helper = $helper;
    }

    public function index(ExchangeDirectionsDataTable $dataTable)
    {
        $data = ['menu' => 'crypto_exchange', 'sub_menu' => 'exchange_directions'];
        if (!m_g_c_v('Q1JZUFRPRVhDSEFOR0VfU0VDUkVU') && m_aic_c_v('Q1JZUFRPRVhDSEFOR0VfU0VDUkVU')) {
            return view('addons::install', ['module' => 'Q1JZUFRPRVhDSEFOR0VfU0VDUkVU']);
        }
        return $dataTable->render('cryptoexchange::admin.direction.list', $data);
    }

    public function create()
    {
        $data = ['menu' => 'crypto_exchange', 'sub_menu' => 'exchange_directions'] ;
        $data['currencies'] = \App\Models\Currency::where(['status' => 'Active', 'type' => 'fiat'])->get();
        return view('cryptoexchange::admin.direction.create', $data);
    }

    public function store(StoreDirectionRequest $request)
    {
        $pairCheck = currencyPairCheck($request->from_currency_id, $request->to_currency_id, $request->direction_type);
        if (!$pairCheck) {
            $this->helper->one_time_message('error', __('Invalid currency pair.'));
            return redirect()->route('admin.crypto_direction.index');
        }
        $checkDirection = ExchangeDirection::getDirection($request->from_currency_id, $request->to_currency_id);
        if (!empty($checkDirection)) {
            $this->helper->one_time_message('error', __('Direction already exist.'));
            return redirect()->route('admin.crypto_direction.index');
        }
        $direction                      = new ExchangeDirection();
        $direction->from_currency_id    = $request->from_currency_id;
        $direction->to_currency_id      = $request->to_currency_id;
        $direction->exchange_rate       = (isset($request->exchange_rate) && ($request->exchange_from == 'local')) ?  $request->exchange_rate : NULL;
        $direction->fees_percentage     = $request->fees_percentage;
        $direction->fees_fixed          = $request->fees_fixed;
        $direction->min_amount          = $request->min_amount;
        $direction->max_amount          = $request->max_amount;
        $direction->payment_instruction = $request->payment_instruction;
        $direction->exchange_from       = ($request->exchange_from == 'api') ? 'api' : 'local';
        $direction->gateways            = isset($request->gateway) ?  implode(',', $request->gateway) : NULL;
        $direction->type                = $request->direction_type;
        $direction->status              = $request->status;
        $direction->save();
        $this->helper->one_time_message('success', __('Exchange direction created successfully.'));
        return redirect()->route('admin.crypto_direction.index');
    }

    public function edit($id)
    {
        $data = ['menu' => 'crypto_exchange', 'sub_menu' => 'exchange_directions'] ;
        $data['direction'] = $direction = ExchangeDirection::findOrFail($id);
        if (optional($direction->fromCurrency)->status == 'Inactive' || optional($direction->toCurrency)->status == 'Inactive') {
            $direction->status = 'Inactive';
        }
        $type = ($direction->type == 'crypto_buy') ? 'fiat' : 'crypto';
        $data['currencies'] = \App\Models\Currency::where(['status' => 'Active', 'type' => $type])->orWhere('id', $direction->from_currency_id)->get();
        $data['gateways'] = ExchangeDirection::currencyPaymentMethodList($direction->from_currency_id);
        $data['preference'] = ($direction->type == 'crypto_buy') ? preference('decimal_format_amount', 2) :  preference('decimal_format_amount_crypto', 8);
        $data['rate_preference'] = ($direction->type == 'crypto_sell') ? preference('decimal_format_amount', 2) :  preference('decimal_format_amount_crypto', 8);
        return view('cryptoexchange::admin.direction.edit', $data);
    }

    public function update(StoreDirectionRequest $request, $id)
    {
        $pairCheck = currencyPairCheck($request->from_currency_id, $request->to_currency_id, $request->direction_type);
        if (!$pairCheck) {
            $this->helper->one_time_message('error', __('Invalid currency pair.'));
            return redirect()->route('admin.crypto_direction.index');
        }
        $exist_direction = ExchangeDirection::where('id', '!=', $id)->where(['from_currency_id' => $request->from_currency_id, 'to_currency_id' => $request->to_currency_id, 'type' => $request->direction_type])->get();
        $from_currency= \App\Models\Currency::find($request->from_currency_id);
        $to_currency= \App\Models\Currency::find($request->to_currency_id);
        if ($from_currency->status == 'Inactive') {
            $this->helper->one_time_message('error', __('The currency :x is inactive, please activate.', ['x' => $from_currency->code]));
            return redirect()->back()->withInput();
        }
        if ( $to_currency->status == 'Inactive') {
            $this->helper->one_time_message('error', __('The currency :x is inactive, please activate.', ['x' => $to_currency->code]));
            return redirect()->back()->withInput();
        }
        if (count($exist_direction)) {
            $this->helper->one_time_message('error', __('Exchange direction already exist.'));
            return redirect()->route('admin.crypto_direction.index');
        }
        $direction                      = ExchangeDirection::find($id);
        $direction->from_currency_id    = $request->from_currency_id;
        $direction->to_currency_id      = $request->to_currency_id;
        $direction->exchange_rate       = (isset($request->exchange_rate) && ($request->exchange_from == 'local')) ?  $request->exchange_rate : NULL;
        $direction->fees_percentage     = $request->fees_percentage;
        $direction->fees_fixed          = $request->fees_fixed;
        $direction->min_amount          = $request->min_amount;
        $direction->max_amount          = $request->max_amount;
        $direction->payment_instruction = $request->payment_instruction;
        $direction->exchange_from       = $request->exchange_from;
        $direction->gateways            = isset($request->gateway) ? implode(',', $request->gateway) : NULL;
        $direction->status              = $request->status;
        $direction->save();
        $this->helper->one_time_message('success', __('Exchange direction edited successfully.'));
        return redirect()->route('admin.crypto_direction.index');
    }

    public function delete($id)
    {
        $direction = ExchangeDirection::find($id);
        $transaction = CryptoExchange::where(['from_currency' => $direction->from_currency_id, 'to_currency' => $direction->to_currency_id ])->first();
        if (isset($transaction)) {
            $this->helper->one_time_message('error', __('Sorry, You can\'t delete this direction, it\'s transaction exists.'));
            return redirect()->route('admin.crypto_direction.index');
        }
        $direction->delete();
        $this->helper->one_time_message('success', __('Exchange direction deleted successfully.'));
        return redirect()->route('admin.crypto_direction.index');
    }

    public function getCurrency()
    {
        $fromCurrencyId = intval(request()->from_currency_id);
        // direction type - crypto sell or crypto exchange, currency type will be crypto
        $currencyType = (request()->direction_type == 'crypto_buy') ? 'fiat':'crypto';
        $toCurrencyType = (request()->direction_type == 'crypto_sell') ? 'fiat':'crypto';     
        // If direction type crypto_buy, all fromCurrencies will be the fiat
        // If direction type crypto_sell or crypto_exchange, all fromCurrencies will be the crypto
        $fromCurrencies = \App\Models\Currency::where(['type' => $currencyType, 'status' => 'Active'])->get();
        // If crypto_buy or sell - from currencies fiat then tocurrencies crypto & vice versa
        $toCurrencies = \App\Models\Currency::where(['type' => $toCurrencyType, 'status' => 'Active']);
        // Get all the toCurrencies that already exist in direction based on selected fromCurrency
        $direction = ExchangeDirection::where('from_currency_id', $fromCurrencyId)->pluck('to_currency_id');
        // Dropping those currencies from - toCurrencies those already used in direction
        $toCurrencies = $toCurrencies->whereNotIn('id', $direction)->get();
        // Also drop the from currency, from the toCurrencies list
        if (request()->direction_type == 'crypto_swap') {
            $toCurrencies = $toCurrencies->except($fromCurrencyId);
        }
        return response()->json([
            'toCurrencies' => $toCurrencies, 
            'fromCurrencies' => $fromCurrencies, 
            'direction' => $direction
        ]);
    }

    public function directionGateway(Request $request)
    {
        $fromCurrencyId = $request->from_currency_id;
        $paymentMethod = ExchangeDirection::currencyPaymentMethodList($fromCurrencyId);
        $status = count($paymentMethod) ? 200 : 400 ;
        return response()->json(['status' => $status, 'paymentMethod' => $paymentMethod]);
    }

}
