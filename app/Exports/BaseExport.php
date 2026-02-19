<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;

/**
 * Base Export class template for maatwebsite/excel v3
 * 
 * Migration from v2 to v3:
 * 
 * V2 (Old):
 *   Excel::create('filename', function($excel) use ($data) {
 *       $excel->sheet('Sheet1', function($sheet) use ($data) {
 *           $sheet->fromArray($data);
 *       });
 *   })->export('xlsx');
 * 
 * V3 (New):
 *   return Excel::download(new MyExport($data), 'filename.xlsx');
 */
class BaseExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize
{
    protected Collection $data;
    protected array $headings;
    protected string $title;

    public function __construct(array|Collection $data, array $headings = [], string $title = 'Sheet1')
    {
        $this->data = $data instanceof Collection ? $data : collect($data);
        $this->headings = $headings;
        $this->title = $title;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection(): Collection
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return $this->headings;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }
}
