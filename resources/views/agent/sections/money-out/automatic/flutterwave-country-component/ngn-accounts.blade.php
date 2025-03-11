<div class="col-lg-12 form-group">
    <label for="bank_name">{{ __("select Bank") }} <span class="text-danger">*</span></label>
    <select name="bank_name" id="bank_name" class="form--control select2-basic" required data-placeholder="{{ __("select Bank") }}" >
          <option disabled selected value="">{{ __("select Bank") }}</option>
        @foreach ($allBanks ??[] as $bank)
            <option value="{{ $bank['code'] }}" data-bank-id="{{ $bank['id'] }}">{{ $bank['name'] }}</option>
        @endforeach
    </select>
</div>
<div class="branches-list">
    @if($branch_status)
        <div class="col-lg-12 form-group">
            <label for="branch_code">{{ __("Bank Branch") }} <span class="text-danger">*</span></label>
            <select name="branch_code" class="form--control select2-basic" required data-placeholder="{{ __("Select Bank Branch") }}">
                <option disabled selected value="">{{ __("Select Bank Branch") }}</option>
            </select>
        </div>
    @endif
</div>
<div class="col-lg-12 form-group">
    <label for="account_number">{{ __("account Number") }} <span class="text-danger">*</span></label>
    <input type="text" class="form--control check_bank number-input" id="account_number"  name="account_number" value="{{ old('account_number') }}" placeholder="{{ __("enter Account Number") }}">
    <label class="exist text-start"></label>
</div>

