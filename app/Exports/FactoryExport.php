<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FactoryExport implements FromCollection, WithHeadings, ShouldAutoSize
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
        return collect($this->data)->flatMap(function ($factory) {
            $rows = [];

            // Parent row
            $rows[] = [
                'ქარხანა' => $factory->factory,
                'მოხელე' => $factory->craftsman,
                'დაზიანება' => $factory->damage,
                'დეტალი' => $factory->detail_name,
                'რაოდენობა' => $factory->quantity,
                'დეტალის ფასი' => money($factory->detail_price),
                'ხელობის ფასი' => money($factory->craft_price),
                'დამატებითი ხარჯი' => money($factory->additional_expense),
                'ჯამური ფასი' => money($factory->total_price),
                'კომენტარი' => $factory->comment,
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
            'ქარხანა',
            'მოხელე',
            'დაზიანება',
            'დეტალი',
            'რაოდენობა',
            'დეტალის ფასი',
            'ხელობის ფასი',
            'დამატებითი ხარჯი',
            'ჯამური ფასი',
            'კომენტარი',
        ];
    }
}
