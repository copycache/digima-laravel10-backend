<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;

/**
 * Dragonpay Orders Export
 */
class DragonpayOrdersExport implements FromView, WithTitle, ShouldAutoSize
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('export.excel.Dragonpay.AdminDragonpayOrders_xls', $this->data);
    }

    public function title(): string
    {
        return 'Dragonpay Orders';
    }
}
