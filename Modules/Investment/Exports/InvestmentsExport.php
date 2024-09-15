<?php

namespace Modules\Investment\Exports;

use Modules\Investment\Entities\Invest;
use Maatwebsite\Excel\Concerns\{
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles
};

class InvestmentsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function query()
    {
        $from     = (isset(request()->startfrom) && !empty(request()->startfrom)) ? setDateForDb(request()->startfrom) : null;
        $to       = (isset(request()->endto) && !empty(request()->endto)) ? setDateForDb(request()->endto) : null;
        $status   = isset(request()->status) ? request()->status : null;
        $pm       = isset(request()->payment_methods) ? request()->payment_methods : null;
        $currency = isset(request()->currency) ? request()->currency : null;
        $user     = isset(request()->user_id) ? request()->user_id : null;
        $investments = (new Invest())->getInvestmentsList($from, $to, $status, $currency, $pm, $user)->orderBy('id', 'desc');

        return $investments;
    }

    public function headings(): array
    {
        return [
            'Plan',
            'User',
            'Currency',
            'Payment Method',
            'Amount',
            'Estimate Profit',
            'Total',
            'Total Term',
            'Start Time',
            'End Time',
            'Status',
        ];
    }

    public function map($investments): array
    {
        return [
            getColumnValue($investments->investmentPlan, 'name'),
            getColumnValue($investments->user),
            getColumnValue($investments->currency, 'code'),
            (optional($investments->paymentMethod)->name == 'Mts' ? __('Wallet') : getColumnValue($investments->paymentMethod, 'name')),
            formatNumber($investments->amount, $investments->currency_id),
            formatNumber($investments->estimate_profit, $investments->currency_id),
            formatNumber($investments->total, $investments->currency_id),
            $investments->term_total,
            dateFormat($investments->start_time),
            dateFormat($investments->end_time),
            $investments->status
        ];
    }

    public function styles($transfer)
    {
        $transfer->getStyle('A:B')->getAlignment()->setHorizontal('center');
        $transfer->getStyle('C:D')->getAlignment()->setHorizontal('center');
        $transfer->getStyle('E:F')->getAlignment()->setHorizontal('center');
        $transfer->getStyle('G:H')->getAlignment()->setHorizontal('center');
        $transfer->getStyle('1')->getFont()->setBold(true);
    }
}
