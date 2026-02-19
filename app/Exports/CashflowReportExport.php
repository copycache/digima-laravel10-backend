<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;

/**
 * Cashflow Report Export
 */
class CashflowReportExport implements FromView, WithTitle, ShouldAutoSize
{
    protected $lists;

    public function __construct($lists)
    {
        $this->lists = $lists;
    }

    public function view(): View
    {
        return view('export.excel.adminCashflowReportxls', [
            '_list' => $this->lists
        ]);
    }

    public function title(): string
    {
        return 'Cashflow Report';
    }
}
