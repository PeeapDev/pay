<?php

namespace Modules\CryptoExchange\Datatables;

use App\Http\Helpers\Common;
use Modules\CryptoExchange\Entities\CryptoExchange;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Http\JsonResponse;

class CryptoExchangesDataTable extends DataTable
{
    public function ajax(): JsonResponse
    {
        return datatables()
            ->of($this->query())
            ->editColumn('created_at', function ($cryptoExchange) {
                return dateFormat($cryptoExchange->created_at);
            })
            ->addColumn('user_id', function ($cryptoExchange) {
                $sender = getColumnValue($cryptoExchange->user);
                if ($sender <> '-' && Common::has_permission(auth('admin')->id(), 'edit_user')) {
                    return '<a href="' . url(config('adminPrefix') . '/users/edit/' . optional($cryptoExchange->user)->id) . '">' . $sender . '</a>';
                }
                return $cryptoExchange->email_phone;
            })
            ->editColumn('type', function ($cryptoExchange) {
                $transaction_type = getColumnValue(optional($cryptoExchange->transaction)->transaction_type, 'name');
                return  ucwords((str_replace('_', ' ',  $transaction_type)));
            })
            ->editColumn('amount', function ($cryptoExchange) {
                return moneyFormat(optional($cryptoExchange->fromCurrency)->symbol, formatNumber($cryptoExchange->amount, $cryptoExchange->from_currency));
            })
            ->editColumn('fee', function ($cryptoExchange) {
                return ($cryptoExchange->fee == 0) ? '-' : formatNumber($cryptoExchange->fee, $cryptoExchange->from_currency);
            })
            ->editColumn('exchange_rate', function ($cryptoExchange) {
                return moneyFormat(optional($cryptoExchange->toCurrency)->symbol, formatNumber($cryptoExchange->exchange_rate, $cryptoExchange->to_currency));
            })
            ->editColumn('get_amount', function ($cryptoExchange) {
                return moneyFormat(optional($cryptoExchange->toCurrency)->symbol, formatNumber($cryptoExchange->get_amount, $cryptoExchange->to_currency));
            })
            ->addColumn('from_currency', function ($cryptoExchange) {
                return optional($cryptoExchange->fromCurrency)->code;
            })
            ->addColumn('to_currency', function ($cryptoExchange) {
                return optional($cryptoExchange->toCurrency)->code;
            })
            ->editColumn('status', function ($cryptoExchange) {
                return getStatusLabel($cryptoExchange->status);
            })
            ->addColumn('action', function ($cryptoExchange) {
                return (Common::has_permission(auth('admin')->id(), 'edit_crypto_exchange_transaction')) ? '<a href="' . route('admin.crypto_exchanges.edit', $cryptoExchange->id). '" class="btn btn-xs btn-primary"><i class="fa fa-edit"></i></a>&nbsp;' : '';
            })
            ->rawColumns(['user_id', 'total', 'status', 'action', 'amount'])
            ->make(true);
    }

    public function query()
    {
        $status   = isset(request()->status) ? request()->status : 'all';
        $currency = isset(request()->currency) ? request()->currency : 'all';
        $user     = isset(request()->user_id) ? request()->user_id : null;
        $from     = isset(request()->from) && !empty(request()->from) ? setDateForDb(request()->from) : null;
        $to       = isset(request()->to) && !empty(request()->to) ? setDateForDb(request()->to) : null;
        $query    = (new CryptoExchange())->getExchangesList($from, $to, $status, $currency, $user);

        return $this->applyScopes($query);
    }

    public function html()
    {
        return $this->builder()
            ->addColumn(['data' => 'id', 'name' => 'crypto_exchanges.id', 'title' => __('ID'), 'searchable' => false, 'visible' => false])
            ->addColumn(['data' => 'uuid', 'name' => 'uuid', 'title' => __('Unique ID'), 'visible' => false])
            ->addColumn(['data' => 'created_at', 'name' => 'created_at', 'title' => __('Date')])
            ->addColumn(['data' => 'user_id', 'name' => 'user.last_name', 'title' => __('User'), 'visible' => false])
            ->addColumn(['data' => 'user_id', 'name' => 'user.first_name', 'title' => __('User')])
            ->addColumn(['data' => 'type', 'name' => 'type', 'title' => __('Type')])
            ->addColumn(['data' => 'amount', 'name' => 'amount', 'title' => __('Amount')])
            ->addColumn(['data' => 'fee', 'name' => 'fee', 'title' => __('Fees')])
            ->addColumn(['data' => 'exchange_rate', 'name' => 'exchange_rate', 'title' => __('Rate')])
            ->addColumn(['data' => 'get_amount', 'name' => 'get_amount', 'title' => __('Get Amount')])
            ->addColumn(['data' => 'from_currency', 'name' => 'fromCurrency.code', 'title' => __('From')])
            ->addColumn(['data' => 'to_currency', 'name' => 'toCurrency.code', 'title' => __('To')])
            ->addColumn(['data' => 'status', 'name' => 'status', 'title' => __('Status')])
            ->addColumn(['data' => 'action', 'name' => 'action', 'title' => __('Action'), 'orderable' => false, 'searchable' => false])
            ->parameters(dataTableOptions());
    }
}
