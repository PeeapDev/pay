@if (admin_permission_by_name("admin.currency.precision.setup"))
    <div id="setupPrecison" class="mfp-hide medium">
        <div class="modal-data">
            <div class="modal-header px-0">
                <h5 class="modal-title">{{ __("Setup Decimal Precison") }}</h5>
            </div>
            <div class="modal-form-data">
                <form class="modal-form" method="POST" action="{{ setRoute('admin.currency.precision.setup') }}">
                    @csrf
                    @method("PUT")
                    <div class="row mb-10-none mt-3">
                        <div class="col-xl-12 col-lg-12 form-group">
                            <label for="">{{ __("Fiat Precision") }}  <span class="text--warning"> ({{ __("Up to 10") }})</span></label>
                            <input type="number" placeholder="{{  __("Write Here..") }}" name="fiat_precision_value" class="form--control" value="{{ $basic_settings->fiat_precision_value }}">
                            <span class="fw-bold fiat-example">--</span>
                        </div>
                        <div class="col-xl-12 col-lg-12 form-group">
                            <label for="">{{ __("Crypto Precision") }}  <span class="text--warning"> ({{ __("Up to 10") }})</span></label>
                            <input type="number" placeholder="{{  __("Write Here..") }}" name="crypto_precision_value" class="form--control" value="{{ $basic_settings->crypto_precision_value }}">
                             <span class="fw-bold crypto-example">--</span>
                        </div>

                        <div class="col-xl-12 col-lg-12 form-group d-flex align-items-center justify-content-between mt-4">
                            <button type="button" class="btn btn--danger modal-close">{{ __("Cancel") }}</button>
                            <button type="submit" class="btn btn--base">{{ __("update") }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@push('script')
    <script>
        $(document).ready(function(){
            set_example_value();
        });

        $("input[name=fiat_precision_value]").keyup(function(){
            set_example_value();
        });

        $("input[name=crypto_precision_value]").keyup(function(){
            set_example_value();
        });
        function acceptVar() {
           var fPrecionValue = $("input[name=fiat_precision_value]").val();
           var cPrecionValue = $("input[name=crypto_precision_value]").val();

           return {
                fPrecionValue:fPrecionValue,
                cPrecionValue:cPrecionValue,
           };
       }
       function set_example_value(){
        var exampleAmount = 100;
            var fValue = acceptVar().fPrecionValue;
            var cValue = acceptVar().cPrecionValue;


            fValue = fValue === '' ? '' : Math.min(Math.max(parseInt(fValue) || 1, 1), 10);
            cValue = cValue === '' ? '' : Math.min(Math.max(parseInt(cValue) || 1, 1), 10);

            $("input[name=fiat_precision_value]").val(fValue);
            $("input[name=crypto_precision_value]").val(cValue);


            var fExample = fValue === '' ? 2 : fValue;
            var cExample = cValue === '' ? 8 : cValue;

            $(".fiat-example").text("{{ __('Example') }}" + " : "+ parseFloat(exampleAmount).toFixed(fExample));
            $(".crypto-example").text("{{ __('Example') }}" + " : "+ parseFloat(exampleAmount).toFixed(cExample));
       }

    </script>
@endpush

