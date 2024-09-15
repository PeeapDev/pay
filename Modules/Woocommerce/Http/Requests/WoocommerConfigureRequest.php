<?php

namespace Modules\Woocommerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WoocommerConfigureRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'plugin_brand' => 'required|max:50',
            'plugin_name' => 'required|max:91',
            'plugin_uri' => 'required|url|max:191',
            'plugin_author' => 'required|max:50',
            'publication_status' => 'required',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
