<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;

/**
 * Top Recruiter Export
 */
class TopRecruiterExport implements FromView, WithTitle, ShouldAutoSize
{
    protected $lists;
    protected string $dateFrom;
    protected string $dateTo;

    public function __construct($lists, string $dateFrom = '', string $dateTo = '')
    {
        $this->lists = $lists;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function view(): View
    {
        return view('export.excel.adminTopRecruiterReportxls', [
            '_list' => $this->lists,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
        ]);
    }

    public function title(): string
    {
        return 'Top Recruiter';
    }
}
