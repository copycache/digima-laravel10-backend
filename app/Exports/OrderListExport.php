<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;

/**
 * Order List Export
 */
class OrderListExport implements FromView, WithTitle, ShouldAutoSize
{
    protected array $data;
    protected string $status;

    public function __construct(array $data, string $status = '')
    {
        $this->data = $data;
        $this->status = $status;
    }

    public function view(): View
    {
        return view('export.excel.adminOrderList_xls', [
            '_list' => $this->data,
            'status' => $this->status
        ]);
    }

    public function title(): string
    {
        return 'Order List - ' . strtoupper($this->status);
    }
}
