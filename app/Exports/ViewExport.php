<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;

/**
 * View-based Export class template for maatwebsite/excel v3
 * 
 * Use this when you want to export using a Blade view template
 * (useful for complex formatting that matches existing export views)
 */
class ViewExport implements FromView, WithTitle, ShouldAutoSize
{
    protected string $view;
    protected array $data;
    protected string $title;

    public function __construct(string $view, array $data = [], string $title = 'Sheet1')
    {
        $this->view = $view;
        $this->data = $data;
        $this->title = $title;
    }

    /**
     * @return View
     */
    public function view(): View
    {
        return view($this->view, $this->data);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }
}
