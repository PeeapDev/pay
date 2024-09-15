<?php

namespace Modules\TatumIo\Datatables;

use Yajra\DataTables\Services\DataTable;
use Illuminate\Http\JsonResponse;
use Modules\TatumIo\Class\{
    CryptoNetwork,
    TatumIoTransaction
};
use App\Models\User;

class WebhookListDataTable extends DataTable
{
    protected $network;

    public function ajax(): JsonResponse
    {
        return datatables()
            ->of($this->query())
            ->addColumn('user', function ($subscriptionList) {
                return User::getUserByAddress($subscriptionList->attr->address);
            })
            ->addColumn('action', function ($subscriptionList) {
                return '<a href="' . url(config('adminPrefix').'/tatumio/webhook/'.$this->network.'/' . $subscriptionList->id) . '" class="btn btn-xs btn-danger delete-warning"><i class="fa fa-trash"></i></a>';
            })
            ->rawColumns(['user', 'action'])
            ->make(true);
    }

    public function query()
    {
        $this->network   = decrypt(request()->network);
        $tatumIo = new TatumIoTransaction($this->network);
        $tatumIo->tatumIoAsset();
        $api_key = $tatumIo->getMerchantApiKey();
        $cryptoNetwork = new CryptoNetwork($api_key, $this->network);
        return $cryptoNetwork->subscriptionList();
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'attr.chain', 'name' => 'chain', 'title' => __('Chain')])
            ->addColumn(['data' => 'type', 'name' => 'type', 'title' => __('Type')])
            ->addColumn(['data' => 'id', 'name' => 'id', 'title' => __('Subscription ID')])
            ->addColumn(['data' => 'attr.address', 'name' => 'attr.address', 'title' => __('Address')])
            ->addColumn(['data' => 'user', 'name' => 'user', 'title' => __('User')])
            ->addColumn(['data' => 'attr.url', 'name' => 'url', 'title' => __('Url')])
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => __('Action'), 'orderable' => false, 'searchable' => false])
            ->parameters(dataTableOptions());
    }
}
