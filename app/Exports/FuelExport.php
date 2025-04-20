<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FuelExport implements FromCollection, WithHeadings, ShouldAutoSize
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
        return collect($this->data)->flatMap(function ($fuel) {
            $rows = [];

            // Parent row
            $rows[] = [
                'დასახელება' => $fuel->title,
                'ფასი' => money($fuel->price),
                'რაოდენობა' => $fuel->quantity,
                'ნაშთი' => $fuel->remain,
                'ჯამური ფასი' => money($fuel->total_price),
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
            'დასახელება',
            'ფასი',
            'რაოდენობა',
            'ნაშთი',
            'ჯამური ფასი',
        ];
    }
}
