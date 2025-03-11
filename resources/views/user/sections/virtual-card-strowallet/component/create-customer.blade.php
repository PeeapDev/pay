<div class="col-xl-8">
    <div class="dash-payment-item-wrapper">
        <div class="dash-payment-item active">
            <div class="dash-payment-title-area">
                <span class="dash-payment-badge">!</span>
                <h5 class="title">{{ __("Create Card Customer") }}</h5>
            </div>
            <div class="dash-payment-body">
                <div class=" mt-20 ">
                    <form class="card-form row" action="{{ route('user.strowallet.virtual.card.create.customer') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="p-3">
                            <div class="row">
                                <div class="col-xl-6 col-lg-6 form-group">
                                    @include('admin.components.form.input', [
                                        'label'         => __("first Name")." "."<span class='text--base'>* <small>(" . __("Should match with your ID") . ")</small></span>",
                                        'placeholder'   => __("enter First Name"),
                                        'name'          => "first_name",
                                        'value'         => old('first_name')
                                    ])
                                </div>
                                <div class="col-xl-6 col-lg-6 form-group">
                                    @include('admin.components.form.input', [
                                        'label'         => __("last Name")." "."<span class='text--base'>* <small>(" . __("Should match with your ID") . ")</small></span>",
                                        'placeholder'   => __("enter Last Name"),
                                        'name'          => "last_name",
                                        'value'         => old('last_name')
                                    ])
                                </div>
                                <div class="col-xl-6 col-lg-6 form-group">
                                    <label>{{ __("Phone") }}<span class="text--base">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text copytext">+{{ $user->mobile_code??"" }}</span>
                                        </div>
                                        <input type="text" class="form--control prepend" placeholder="{{ __('Enter phone number') }}" value="{{ $user->mobile??"" }}" readonly />
                                    </div>
                                </div>
                                <div class="col-xl-6 col-lg-6 form-group">
                                    <label>{{ __("Email") }}<span class="text--base">*</span></label>
                                    <input type="email" class="form--control" name="customer_email" placeholder="{{ __('enter Customer Email') }}" value="{{ old('customer_email',$user->email) }}"/>
                                </div>
                                <div class="col-xl-6 col-lg-6 form-group">
                                    @include('admin.components.form.input', [
                                        'label'         => __("date Of Birth")." "."<span class='text--base'>* <small>(" . __("Should match with your ID") . ")</small></span>",
                                        'type'          => "date",
                                        'name'          => "date_of_birth",
                                        'value'         => old('date_of_birth')
                                    ])
                                </div>
                                <div class="col-xl-6 col-lg-6 form-group">
                                    @include('admin.components.form.input', [
                                        'label'         => __("house Number")." "."<span class='text--base'>*</span>",
                                        'placeholder'   => __("enter House Number"),
                                        'name'          => "house_number",
                                        'value'         => old('house_number')
                                    ])
                                </div>
                                <div class="col-xl-6 col-lg-6 form-group">
                                    @include('admin.components.form.input', [
                                        'label'         => __("address")." "."<span class='text--base'>*</span>",
                                        'placeholder'   => __("enter Address"),
                                        'name'          => "address",
                                        'value'         => old('address')
                                    ])
                                </div>
                                <div class="col-xl-6 col-lg-6 form-group">
                                    @include('admin.components.form.input', [
                                        'label'         => __("zip Code")." "."<span class='text--base'>*</span>",
                                        'placeholder'   => __("enter Zip Code"),
                                        'name'          => "zip_code",
                                        'value'         => old('zip_code', $user->address->zip ?? "")
                                    ])
                                </div>
                                <div class="col-xl-12 col-lg-12 form-group">
                                    @include('admin.components.form.input-file', [
                                        'label'         => __("ID Card Image (Font Side)")." "."<span class='text--base'>* (" . __("NID/Passport") . ")</span>",
                                        'name'          => "id_image_font",
                                        'class'         => "form--control",
                                        'label_class'         => "mw-100"
                                    ])
                                </div>
                                <div class="col-xl-12 col-lg-12 form-group">
                                    @include('admin.components.form.input-file', [
                                        'label'         => __("Your Photo")." "."<span class='text--base'>* (" . __("Should show your face and must be match with your ID") . ")</span>",
                                        'name'          => "user_image",
                                        'class'         => "form--control",
                                        'label_class'         => "mw-100"
                                    ])
                                </div>
                            </div>
                            <div class="col-xl-12 col-lg-12">
                                <button type="submit" class="btn--base w-100 btn-loading">{{ __("Submit") }}</button>
                            </div>
                        </div>
                </form>
                </div>

            </div>
        </div>
    </div>
</div>
