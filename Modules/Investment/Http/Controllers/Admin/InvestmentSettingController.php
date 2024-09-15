<?php

namespace Modules\Investment\Http\Controllers\Admin;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller; 
use App\Http\Helpers\Common;
use Illuminate\Http\Request;
use App\Models\Setting;
use Validator, Cache;

class InvestmentSettingController extends Controller
{
    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function add()
    {
        $data['menu'] = 'investments';
        $data['sub_menu'] = 'investment_settings';
        $data['result'] = settings('investment');
        return view('investment::admin.setting', $data);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $rules = array(
            'schema_display' => 'required',
            'plan_description' => 'required',
            'admin_investment_plan_view' => 'required',
        );

        $fieldNames = array(
            'schema_display' => __('Schema display'),
            'plan_description' => __('Plan description'),
            'admin_investment_plan_view' => __('Admin investment plan view')
        );

        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {
            if (!m_g_c_v('SU5WRVNUTUVOVF9TRUNSRVQ=') && m_ast_c_v('SU5WRVNUTUVOVF9TRUNSRVQ=')) {
                return view('addons::install', ['module' => 'SU5WRVNUTUVOVF9TRUNSRVQ=']);
            }

            Setting::where(['name' => 'schema_display', 'type' => 'investment'])->update(['name' => 'schema_display', 'type' => 'investment', 'value' => $request->schema_display]);

            Setting::where(['name' => 'plan_description', 'type' => 'investment'])->update(['name' => 'plan_description', 'type' => 'investment', 'value' => $request->plan_description]);

            Setting::where(['name' => 'kyc', 'type' => 'investment'])->update(['name' => 'kyc', 'type' => 'investment', 'value' => $request->isEnabled == 'on' ? 'Yes' :  'No']);

            Setting::where(['name' => 'invest_start_on_admin_approval', 'type' => 'investment'])->update(['name' => 'invest_start_on_admin_approval', 'type' => 'investment', 'value' => $request->invest_on_admin_approval == 'on' ? 'Yes' :  'No']);

            Setting::where(['name' => 'admin_investment_plan_view', 'type' => 'investment'])->update(['name' => 'admin_investment_plan_view', 'type' => 'investment', 'value' => $request->admin_investment_plan_view]);

            Cache::forget(config('cache.prefix') . '-settings');
            
            (new Common())->one_time_message('success', __("Investment settings updated successfully."));
            return redirect()->route('investment_setting.add');
        }
    }
}
