<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;

/**
 * Cash In Export
 */
class CashInExport implements FromView, WithTitle, ShouldAutoSize
{
    protected array $list;

    public function __construct(array $list)
    {
        $this->list = $list;
    }

    public function view(): View
    {
        return view('export.excel.exportCashinList', [
            '_list' => $this->list
        ]);
    }

    public function title(): string
    {
        return 'Cash In';
    }
}
