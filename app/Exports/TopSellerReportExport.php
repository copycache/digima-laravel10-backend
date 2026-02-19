<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;

/**
 * Top Seller Report Export
 */
class TopSellerReportExport implements FromView, WithTitle, ShouldAutoSize
{
    protected $topSellers;

    public function __construct($topSellers)
    {
        $this->topSellers = $topSellers;
    }

    public function view(): View
    {
        return view('export.excel.adminTopSellerReport_xls', [
            'top_sellers' => $this->topSellers
        ]);
    }

    public function title(): string
    {
        return 'Top Sellers';
    }
}
