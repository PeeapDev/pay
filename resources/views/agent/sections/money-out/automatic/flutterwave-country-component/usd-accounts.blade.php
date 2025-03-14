    <div class="col-12 col-lg-12 form-group">
        <label for="bank_name">{{ __("select Bank") }} <span class="text-danger">*</span></label>
        <select name="bank_name" id="bank_name" class="form--control select2-basic" required data-placeholder="{{ __("select Bank") }}" >
            <option disabled selected value="">{{ __("select Bank") }}</option>
            @foreach ($allBanks ??[] as $bank)
                <option value="{{ $bank['name'] }}" data-bank-id="{{ $bank['id'] }}">{{ $bank['name'] }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12 col-lg-6 form-group">
        <label for="account_number">{{ __("account Number") }} <span class="text-danger">*</span></label>
        <input type="text" class="form--control check_bank number-input" id="account_number"  name="account_number" value="{{ old('account_number') }}" placeholder="{{ __("enter Account Number") }}">
        <label class="exist text-start"></label>
    </div>
    <div class="col-12 col-lg-6 form-group">
        <label for="routing_number">{{ __("Routing Number") }} <span class="text-danger">*</span></label>
        <input type="text" class="form--control  number-input" id="routing_number"  name="routing_number" value="{{ old('routing_number') }}" placeholder="{{ __("Enter Routing Number") }}">
        <label class="exist text-start"></label>
    </div>
    <div class="col-12 col-lg-6 form-group">
        <label for="swift_code">{{ __("Swift Code") }} <span class="text-danger">*</span></label>
        <input type="text" class="form--control" id="swift_code"  name="swift_code" value="{{ old('swift_code') }}" placeholder="{{ __("Enter Swift Code") }}">
        <label class="exist text-start"></label>
    </div>
    <div class="col-12 col-lg-6 form-group">
        <label for="beneficiary_name">{{ __("Beneficiary Name") }} <span class="text-danger">*</span></label>
        <input type="text" class="form--control" id="beneficiary_name"  name="beneficiary_name" value="{{ old('beneficiary_name') }}" placeholder="{{ __("Beneficiary Name") }}">
    </div>
    <div class="col-12 col-lg-6 form-group">
        <label for="beneficiary_country">{{ __("Beneficiary Country") }} <span class="text-danger">*</span></label>
        <select name="beneficiary_country" id="beneficiary_country" class="form--control select2-basic" required data-placeholder="{{ __("Select Beneficiary Country") }}" >
            <option disabled selected value="">{{ __("Select Beneficiary Country") }}</option>
            @foreach ($countries ??[] as $country)
                <option value="{{ $country->iso2 }}" >{{ $country->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12 col-lg-6 form-group">
        <label for="beneficiary_address">{{ __("Beneficiary Address") }} <span class="text-danger">*</span></label>
        <input type="text" class="form--control" id="beneficiary_address"  name="beneficiary_address" value="{{ old('beneficiary_address') }}" placeholder="{{ __("Enter Beneficiary Address") }}">
    </div>
