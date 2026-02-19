<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;

/**
 * Item Code Export
 */
class ItemCodeExport implements FromView, WithTitle, ShouldAutoSize
{
    protected array $list;
    protected string $itemSku;

    public function __construct(array $list, string $itemSku = '')
    {
        $this->list = $list;
        $this->itemSku = $itemSku;
    }

    public function view(): View
    {
        return view('export.excel.item_code', [
            '_list' => $this->list,
            'data' => ['Code', 'Pin', 'Sold to', 'Transfer to', 'Used by']
        ]);
    }

    public function title(): string
    {
        return 'Item Code - ' . strtoupper($this->itemSku);
    }
}
