<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;

/**
 * Quote Request Export
 */
class QuoteRequestExport implements FromView, WithTitle, ShouldAutoSize
{
    protected array $list;
    protected array $headings;

    public function __construct(array $list)
    {
        $this->list = $list;
        $this->headings = ['Item Name', 'Name', 'Email', 'Phone', 'Message'];
    }

    public function view(): View
    {
        return view('export.excel.quote_request', [
            '_list' => $this->list,
            'data' => $this->headings
        ]);
    }

    public function title(): string
    {
        return 'Quote Request';
    }
}
