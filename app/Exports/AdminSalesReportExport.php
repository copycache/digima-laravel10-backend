<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;

/**
 * Admin Sales Report Export
 */
class AdminSalesReportExport implements FromView, WithTitle, ShouldAutoSize
{
    protected $list;

    public function __construct($list)
    {
        $this->list = $list;
    }

    public function view(): View
    {
        return view('export.excel.adminSalesReportxls', [
            '_list' => $this->list
        ]);
    }

    public function title(): string
    {
        return 'Sales Report';
    }
}
