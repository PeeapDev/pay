<div class="col-12 col-lg-6 form-group">
    <label for="bank_name">{{ __("select Bank") }} <span class="text-danger">*</span></label>
    <select name="bank_name" id="bank_name" class="form--control select2-basic" required data-placeholder="{{ __("select Bank") }}" >
          <option disabled selected value="">{{ __("select Bank") }}</option>
        @foreach ($allBanks ??[] as $bank)
            <option value="{{ $bank['code'] }}" data-bank-id="{{ $bank['id'] }}">{{ $bank['name'] }}</option>
        @endforeach
    </select>
</div>

<div class="col-12 col-lg-6 form-group">
    <label for="account_number">{{ __("account Number") }} <span class="text-danger">*</span></label>
    <input type="text" class="form--control check_bank number-input" id="account_number"  name="account_number" value="{{ old('account_number') }}" placeholder="{{ __("enter Account Number") }}">
    <label class="exist text-start"></label>
</div>
<div class="col-12 col-lg-6 form-group">
    <label for="beneficiary_name">{{ __("Beneficiary Name") }} <span class="text-danger">*</span></label>
    <input type="text" class="form--control" id="beneficiary_name"  name="beneficiary_name" value="{{ old('beneficiary_name') }}" placeholder="{{ __("Beneficiary Name") }}">
</div>

<div class="col-12 col-lg-6 form-group">
    <label for="sender">{{ __("sender") }} <span class="text-danger">*</span></label>
    <input type="text" class="form--control" id="sender"  name="sender" value="{{ old('sender') }}" placeholder="{{ __("Enter Sender Name") }}">
</div>

<div class="col-12 col-lg-6 form-group">
    <label for="sender_country">{{ __("Sender Country") }} <span class="text-danger">*</span></label>
    <select name="sender_country" id="sender_country" class="form--control select2-basic" required data-placeholder="{{ __("Select Sender Country") }}" >
        <option disabled selected value="">{{ __("Select Beneficiary Country") }}</option>
        @foreach ($countries ??[] as $country)
            <option value="{{ $country->iso2 }}" >{{ $country->name }}</option>
        @endforeach
    </select>
</div>


<div class="col-12 col-lg-6 form-group">
    <label for="mobile_number">{{ __("Mobile Number") }} <span class="text-danger">*</span></label>
    <input type="number" class="form--control" id="mobile_number"  name="mobile_number" value="{{ old('mobile_number') }}" placeholder="{{ __("enter Mobile Number") }}">
</div>





