
@if($basic_settings->merchant_kyc_verification)
    @foreach ($kyc_fields as $item)
        @if ($item->type == "select")
            <div class="col-lg-12 form-group">
                <label for="{{ $item->name }}">{{ __($item->label) }}</label>
                <select name="{{ $item->name }}" id="{{ $item->name }}" class="form--control nice-select">
                    <option selected disabled>Choose One</option>
                    @foreach ($item->validation->options as $innerItem)
                        <option value="{{ $innerItem }}">{{ $innerItem }}</option>
                    @endforeach
                </select>
                @error($item->name)
                    <span class="invalid-feedback d-block" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>
        @elseif ($item->type == "file")
            <div class="col-xl-6 col-lg-6 col-md-4 form-group">
                <div class="file-holder-wrapper">
                    @include('admin.components.form.input',[
                        'label'     =>__( $item->label),
                        'name'      => $item->name,
                        'type'      => $item->type,
                        'value'     => old($item->name),
                    ])
                </div>
            </div>
        @elseif ($item->type == "text")
            <div class="col-lg-12 form-group">
                @include('admin.components.form.input',[
                    'label'     => __($item->label),
                    'name'      => $item->name,
                    'type'      => $item->type,
                    'value'     => old($item->name),
                ])
            </div>
        @elseif ($item->type == "textarea")
            <div class="col-lg-12 form-group">
                @include('admin.components.form.textarea',[
                    'label'     => __($item->label),
                    'name'      => $item->name,
                    'value'     => old($item->name),
                ])
            </div>
        @endif
    @endforeach
@endif
