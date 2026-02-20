<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Generic Query Export Class
 * 
 * Use this for large exports where you have a Query Builder instance.
 * Laravel Excel will automatically chunk the query for performance.
 */
class QueryExport implements FromQuery, WithHeadings, WithTitle, ShouldAutoSize, WithMapping
{
    protected $query;
    protected array $headings;
    protected $mapper;
    protected string $sheetTitle;

    /**
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     * @param array $headings Column headings
     * @param callable $mapper Function to map each row: fn($item) => [col1, col2, ...]
     * @param string $sheetTitle Sheet name
     */
    public function __construct($query, array $headings, callable $mapper, string $sheetTitle = 'Sheet1')
    {
        $this->query = $query;
        $this->headings = $headings;
        $this->mapper = $mapper;
        $this->sheetTitle = $sheetTitle;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function map($item): array
    {
        return ($this->mapper)($item);
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }
}
