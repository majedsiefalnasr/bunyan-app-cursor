<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BusinessAnalyticsExport implements FromArray, WithHeadings
{
    /**
     * @param  list<list<string|int|float|null>>  $rows
     * @param  list<string>  $headings
     */
    public function __construct(
        private readonly array $headings,
        private readonly array $rows,
    ) {
    }

    /**
     * @return list<list<string|int|float|null>>
     */
    public function array(): array
    {
        return $this->rows;
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return $this->headings;
    }
}
