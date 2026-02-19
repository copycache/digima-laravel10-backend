<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

/**
 * Generic Dynamic Export Class
 * 
 * Use this for simple exports where you have:
 * - An array/collection of data
 * - Column headings
 * - A mapping function to extract row data
 * 
 * Usage:
 * $export = new DynamicExport(
 *     $data,
 *     ['Name', 'Email', 'Phone'],
 *     function($item) {
 *         return [$item->name, $item->email, $item->phone];
 *     },
 *     'My Sheet'
 * );
 * return Excel::download($export, 'export.xlsx');
 */
class DynamicExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithMapping
{
    protected Collection $data;
    protected array $headings;
    protected $mapper;
    protected string $sheetTitle;

    /**
     * @param mixed $data The data to export (array or collection)
     * @param array $headings Column headings
     * @param callable $mapper Function to map each row: fn($item) => [col1, col2, ...]
     * @param string $sheetTitle Sheet name
     */
    public function __construct($data, array $headings, callable $mapper, string $sheetTitle = 'Sheet1')
    {
        $this->data = collect($data);
        $this->headings = $headings;
        $this->mapper = $mapper;
        $this->sheetTitle = $sheetTitle;
    }

    public function collection(): Collection
    {
        return $this->data;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    /**
     * Map each item using the provided mapper function
     */
    public function map($item): array
    {
        return ($this->mapper)($item);
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }
}
