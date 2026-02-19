<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;

/**
 * Top Pairs/Slots Export
 */
class TopPairsExport implements FromView, WithTitle, ShouldAutoSize
{
    protected array $list;
    protected string $date;

    public function __construct(array $list, string $date = '')
    {
        $this->list = $list;
        $this->date = $date ?: now()->format('Y-m-d');
    }

    public function view(): View
    {
        return view('export.excel.top_pairs', [
            '_list' => $this->list,
            'date' => $this->date,
            'data' => ['Rank no', 'Slot ID', 'Slot no', 'Slot Owner', 'Membership', 'Total Pairs', 'Date']
        ]);
    }

    public function title(): string
    {
        return 'Top Pairs';
    }
}
