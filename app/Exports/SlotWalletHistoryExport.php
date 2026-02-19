<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;

/**
 * Example: Slot Wallet History Export
 * 
 * Usage in controller:
 *   use App\Exports\SlotWalletHistoryExport;
 *   use Maatwebsite\Excel\Facades\Excel;
 *   
 *   return Excel::download(new SlotWalletHistoryExport($data), 'slot_wallet_history.xlsx');
 */
class SlotWalletHistoryExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    protected Collection $data;

    public function __construct(array|Collection $data)
    {
        $this->data = $data instanceof Collection ? $data : collect($data);
    }

    public function collection(): Collection
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Slot No',
            'Transaction Type',
            'Amount',
            'Balance',
            'Date',
            'Reference',
        ];
    }

    public function title(): string
    {
        return 'Wallet History';
    }
}
