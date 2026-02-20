<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\ViewExport;

class DragonpayPayoutExport implements WithMultipleSheets
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->data['methods'] as $method) {
            $sheetData = $this->data; 
            $sheetData['method'] = $method;
            $sheetData['transactions'] = $method->transactions; // Pass directly to view as well for easier access
            
            $sheets[] = new ViewExport('export.excel.dragonpay_payout_sheet', $sheetData, $method->cash_out_method_name);
        }

        return $sheets;
    }
}
