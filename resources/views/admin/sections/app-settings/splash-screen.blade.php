@extends('admin.layouts.master')

@push('css')
    <style>
        .fileholder {
            min-height: 448px !important;
        }

        .fileholder-files-view-wrp.accept-single-file .fileholder-single-file-view,.fileholder-files-view-wrp.fileholder-perview-single .fileholder-single-file-view{
            height: 404px !important;
        }
    </style>
@endpush

@section('page-title')
    @include('admin.components.page-title',['title' => __($page_title)])
@endsection

@section('breadcrumb')
    @include('admin.components.breadcrumb',['breadcrumbs' => [
        [
            'name'  => __("Dashboard"),
            'url'   => setRoute("admin.dashboard"),
        ]
    ], 'active' => __("App Settings")])
@endsection

@section('content')
    <div class="custom-card">
        <div class="card-header">
            <h6 class="title">{{ __("Splash Screen") }}</h6>
        </div>
        <div class="card-body">
            <form class="card-form" method="POST" action="{{ setRoute('admin.app.settings.splash.screen.update') }}" enctype="multipart/form-data">
                @csrf
                @method("PUT")
                <div class="row mb-10-none">
                    <div class="col-xl-4">
                        <div class="row">
                            <div class="col-xl-6 col-lg-2 col-md-3 col-sm-4 form-group">
                                @include('admin.components.form.input-file',[
                                    'label'             => __("Image (User)").': <span class="text--danger">(414*896)</span>',
                                    'class'             => "file-holder",
                                    'name'              => "image",
                                    'old_files_path'    => files_asset_path('splash-images'),
                                    'old_files'         => $app_settings->splash_screen_image,
                                ])
                            </div>
                            <div class="col-xl-6 col-lg-10 col-md-9 col-sm-8">
                                <div class="form-group">
                                    @include('admin.components.form.input',[
                                        'label'         => __("App Version* (User)"),
                                        'name'          => "version",
                                        'attribute'     => "data-limit=15",
                                        'value'         => old('version',$app_settings->version),
                                        'placeholder'   => __( "Write Here.."),
                                    ])
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="row">
                            <div class="col-xl-6 col-lg-2 col-md-3 col-sm-4 form-group">
                                @include('admin.components.form.input-file',[
                                    'label'             => __("Image (Agent):").' <span class="text--danger">(414*896)</span>',
                                    'class'             => "file-holder",
                                    'name'              => "agent_image",
                                    'old_files_path'    => files_asset_path('splash-images'),
                                    'old_files'         => $app_settings->agent_splash_screen_image,
                                ])
                            </div>
                            <div class="col-xl-6 col-lg-10 col-md-9 col-sm-8">
                                <div class="form-group">
                                    @include('admin.components.form.input',[
                                        'label'         => __("App Version* (Agent)"),
                                        'name'          => "agent_version",
                                        'attribute'     => "data-limit=15",
                                        'value'         => old('agent_version',$app_settings->agent_version),
                                        'placeholder'   => __( "Write Here.."),
                                    ])
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="row">
                            <div class="col-xl-6 col-lg-2 col-md-3 col-sm-4 form-group">
                                @include('admin.components.form.input-file',[
                                    'label'             => __("Image (Merchant)").': <span class="text--danger">(414*896)</span>',
                                    'class'             => "file-holder",
                                    'name'              => "merchant_image",
                                    'old_files_path'    => files_asset_path('splash-images'),
                                    'old_files'         => $app_settings->merchant_splash_screen_image,
                                ])
                            </div>
                            <div class="col-xl-6 col-lg-10 col-md-9 col-sm-8">
                                <div class="form-group">
                                    @include('admin.components.form.input',[
                                        'label'         => __("App Version* (Merchant)"),
                                        'name'          => "merchant_version",
                                        'attribute'     => "data-limit=15",
                                        'value'         => old('merchant_version',$app_settings->merchant_version),
                                        'placeholder'   => __( "Write Here.."),
                                    ])
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-12 col-lg-12 form-group">
                        <button type="submit" class="btn--base w-100 btn-loading">{{ __("update") }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('script')

@endpush
