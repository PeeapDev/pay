<?php

namespace Modules\TatumIo\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\CryptoProvider;

class TatumAssetStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $providerId = CryptoProvider::getIdByAlias('TatumIo');
        
        return [
            'name' => 'required|max:50',
            'network' => [
                'required',
                'string',
                'max:10',
                Rule::unique('crypto_asset_settings')->where(function ($query) use ($providerId) {
                    return $query->where('crypto_provider_id', $providerId);
                }),
            ],
            'symbol' => 'required|max:5',
            'logo' => 'image|mimes:jpeg,png,jpg,bmp,ico|max:1024',
            'api_key' => 'required|max:100',
            'status' => 'required|max:8',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('Please provide a crypto network name'),
            'network.required' => __('Please provide a crypto network code'),
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
