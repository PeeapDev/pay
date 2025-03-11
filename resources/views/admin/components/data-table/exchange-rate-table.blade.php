<table class="custom-table provider_name-search-table">
    <thead>
        <tr>
            <th>{{ __('Provider Name') }}</th>
            <th>{{ __("Status") }}</th>
            <th>{{ __("action") }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($providers ?? [] as $key => $item)
            <tr>
                <td>{{ $item->provider }}</td>
                <td>
                    @include('admin.components.form.switcher',[
                        'label'         => false,
                        'name'          => 'status',
                        'options'       => [__("active") => 1 , __("Deactive") => 0],
                        'onload'        => true,
                        'value'         => $item->status,
                        'data_target'   => $item->id,
                        'permission'    => "admin.live.exchange.rate.status.update",
                    ])
                </td>
                <td>
                    @include('admin.components.link.edit-default',[
                        'href'          => setRoute('admin.live.exchange.rate.edit',$item->slug),
                        'permission'    => "admin.live.exchange.rate.edit",
                    ])
                </td>
            </tr>
        @empty
            @include('admin.components.alerts.empty',['colspan' => 3])
        @endforelse
    </tbody>
</table>
@push('script')
    <script>
        switcherAjax("{{ route('admin.live.exchange.rate.status.update') }}");
    </script>
@endpush
