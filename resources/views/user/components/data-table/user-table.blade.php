<table class="custom-table user-search-table">
    <thead>
        <tr>
            <th>{{ __("User Name") }}</th>
            <th>{{ __("Email") }}</th>
            <th>{{ __("phone Number") }}</th>
            <th>{{ __("Refer Code") }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($refer_users as $item)
            <tr>
                <td>{{ @$item->user->username }}</td>
                <td>{{ @$item->user->email }}</td>
                <td>{{ @$item->user->full_mobile }}</td>
                <td>{{ @$item->user->referral_id }}</td>
            </tr>
        @empty
            <td colspan="100%" class="text-center">{{ __("No data found!") }}</td>
        @endforelse
    </tbody>
</table>
