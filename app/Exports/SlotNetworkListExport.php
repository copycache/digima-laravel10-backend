<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;

/**
 * Slot Network List Export
 */
class SlotNetworkListExport implements FromView, WithTitle, ShouldAutoSize
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('export.csv.slot_network_list_csv', $this->data);
    }

    public function title(): string
    {
        return 'Network List';
    }
}
