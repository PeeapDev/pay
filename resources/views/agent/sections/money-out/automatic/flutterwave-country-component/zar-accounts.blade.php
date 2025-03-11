<div class="col-lg-12 form-group">
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
    <label for="first_name">{{ __("first Name") }} <span class="text-danger">*</span></label>
    <input type="text" class="form--control" id="first_name"  name="first_name" value="{{ old('first_name') }}" placeholder="{{ __("enter First Name") }}">
</div>
<div class="col-12 col-lg-6 form-group">
    <label for="last_name">{{ __("last Name") }} <span class="text-danger">*</span></label>
    <input type="text" class="form--control" id="last_name"  name="last_name" value="{{ old('last_name') }}" placeholder="{{ __("enter Last Name") }}">
</div>
<div class="col-12 col-lg-6 form-group">
    <label for="email">{{ __("Email") }} <span class="text-danger">*</span></label>
    <input type="email" class="form--control" id="email"  name="email" value="{{ old('email') }}" placeholder="{{ __("enter Email Address") }}">
</div>
<div class="col-12 col-lg-6 form-group">
    <label for="mobile_number">{{ __("Mobile Number") }} <span class="text-danger">*</span></label>
    <input type="number" class="form--control" id="mobile_number"  name="mobile_number" value="{{ old('mobile_number') }}" placeholder="{{ __("enter Mobile Number") }}">
</div>
<div class="col-12 col-lg-6 form-group">
    <label for="recipient_address">{{ __("Recipient Address") }} <span class="text-danger">*</span></label>
    <input type="text" class="form--control" id="recipient_address"  name="recipient_address" value="{{ old('recipient_address') }}" placeholder="{{ __("Enter Recipient Address") }}">
</div>

