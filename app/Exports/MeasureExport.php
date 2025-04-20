<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MeasureExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected array|Collection $data;

    /**
     * Constructor to pass in the data for export.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Return the collection of data for the export.
     */
    public function collection(): Collection
    {
        return collect($this->data)->flatMap(function ($measure) {
            $rows = [];

            // Parent row
            $rows[] = [
                'სათაური' => $measure->title,
                'მოკ. სათაური' => $measure->short_title,
            ];

            return $rows;
        });
    }

    /**
     * Return the column headings for the export.
     */
    public function headings(): array
    {
        return [
            'სათაური',
            'მოკ. სათაური',
        ];
    }
}
