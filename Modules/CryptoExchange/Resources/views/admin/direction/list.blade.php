@extends('admin.layouts.master')
@section('title', __('Exchange Directions'))

@section('head_style')
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/DataTables/DataTables-1.10.18/css/jquery.dataTables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('public/dist/plugins/DataTables/Responsive-2.2.2/css/responsive.dataTables.min.css') }}">
@endsection

@section('page_content')
  <div class="box box-default">
      <div class="box-body">
        <div class="d-flex justify-content-between">
          <div>
            <div class="top-bar-title padding-bottom pull-left">
              {{ __('Exchange Directions') }}
            </div>
          </div>
          <div>
            @if (Common::has_permission(\Auth::guard('admin')->user()->id, 'add_crypto_direction'))
              <a href="{{ route('admin.crypto_direction.create') }}" class="btn btn-theme f-14"><span class="fa fa-plus"> &nbsp;</span>{{ __('Add Direction') }}</a>
            @endif
          </div>
        </div>
      </div>
  </div>

  <div class="box">
    <div class="box-body">
      <div class="row">
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-body">
                    <div class="table-responsive f-14">
                      {!! $dataTable->table(['class' => 'table table-striped table-hover dt-responsive', 'width' => '100%', 'cellspacing' => '0']) !!}
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('extra_body_scripts')
<script src="{{ asset('public/dist/plugins/DataTables/DataTables-1.10.18/js/jquery.dataTables.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/dist/plugins/DataTables/Responsive-2.2.2/js/dataTables.responsive.min.js') }}" type="text/javascript"></script>
{!! $dataTable->scripts() !!}
@endpush
