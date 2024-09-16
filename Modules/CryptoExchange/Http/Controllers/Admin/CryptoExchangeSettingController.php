<?php

namespace Modules\CryptoExchange\Http\Controllers\Admin;

use Modules\CryptoExchange\Http\Requests\CryptoSettingsRequest;
use App\Http\Controllers\Controller;
use Cache, Common;


class CryptoExchangeSettingController extends Controller
{
    protected $helper;

    public function __construct()
    {
        $this->helper = new Common();
    }

    public function store(CryptoSettingsRequest $request)
    {
        \App\Models\Preference::where(['category' => 'crypto_exchange', 'field' => 'verification'])->update(array('value' => $request->verification));
        \App\Models\Preference::where(['category' => 'crypto_exchange', 'field' => 'available'])->update(array('value' => $request->available));
        \App\Models\Preference::where(['category' => 'crypto_exchange', 'field' => 'transaction_type'])->update(array('value' => $request->transaction_type));
        Cache::forget(config('cache.prefix') . '-preferences');
        $this->helper->one_time_message('success', __('Crypto settings updated successfully.'));
        return redirect()->route('admin.crypto_settings');     
    }

    public function add()
    {
        $data = ['menu' => 'crypto_exchange', 'sub_menu' => 'crypto_settings'];
        $pref = \App\Models\Preference::where('category', 'crypto_exchange')->get();
        $data_arr = [];
        foreach ($pref as $row) {
            $data_arr[$row->category][$row->field] = $row->value;
        }
        $data['prefData'] = $data_arr;
        if (!m_g_c_v('Q1JZUFRPRVhDSEFOR0VfU0VDUkVU') && m_aic_c_v('Q1JZUFRPRVhDSEFOR0VfU0VDUkVU')) {
            return view('addons::install', ['module' => 'Q1JZUFRPRVhDSEFOR0VfU0VDUkVU']);
        }
        return view('cryptoexchange::admin.crypto_exchange.settings', $data);
    }
}
