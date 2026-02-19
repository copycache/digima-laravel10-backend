<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;

/**
 * Cashout/Payout Schedule Export with multiple sheets
 */
class CashoutPayoutExport implements FromView, WithTitle, ShouldAutoSize
{
    protected $method;
    protected array $bankHeadings;
    protected array $remitHeadings;

    public function __construct($method)
    {
        $this->method = $method;
        $this->bankHeadings = ['Slot Code', 'Account Name', 'Account Number', 'Account Type', 'Email', 'Phone Number', 'TIN', 'Tax', 'Method Fee', 'Service Charge', 'Survey Charge', 'Product Charge', 'GC Charge', 'Savings', 'Amount Due', 'Net Payout', 'Date'];
        $this->remitHeadings = ['Slot Code', 'Full Name', 'Full Address', 'Other Info', 'Email', 'Phone Number', 'TIN', 'Tax', 'Method Fee', 'Service Charge', 'Savings', 'Amount Due', 'Net Payout', 'Date'];
    }

    public function view(): View
    {
        $headings = $this->method->cash_out_method_category == 'remittance' 
            ? $this->remitHeadings 
            : $this->bankHeadings;

        return view('export.excel.cashout_payout', [
            'method' => $this->method,
            'headings' => $headings
        ]);
    }

    public function title(): string
    {
        return $this->method->cash_out_method_name ?? 'Cashout';
    }
}
