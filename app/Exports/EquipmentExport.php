<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class EquipmentExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    private array|Collection $data;

    // Constructor to pass in the data for export
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Return the collection of data for the export.
     *
     * @return Collection
     */
    public function collection(): Collection
    {
        return collect($this->data)->flatMap(function ($equipment) {
            $rows = [];

            $rows[] = [
                'ტექნიკა' => $equipment->equipment,
                'ტიპი' => [
                    'main' => 'საკუთარი',
                    'rent' => 'ნაქირავები',
                ][$equipment->type],
                'ფასი' => money($equipment->price),
                'ინფორმაცია' => 'ტექნიკა',
            ];

            foreach ($equipment->damages as $damage) {
                $rows[] = [
                    'ტექნიკა' => null,
                    'ტიპი' => null,
                    'ფასი' => null,
                    'ინფორმაცია' => 'დაზიანება',
                    'მოხელე' => $damage->craftsman,
                    'დაზიანება' => $damage->damage,
                    'დეტალი' => $damage->detail_name,
                    'რაოდენობა' => $damage->quantity,
                    'დეტალის ფასი' => money($damage->detail_price),
                    'ხელობის ფასი' => money($damage->craft_price),
                    'დამატებითი ხარჯი' => money($damage->additional_expense),
                    'ჯამური ფასი' => money($damage->total_price),
                    'კომენტარი' => $damage->comment,
                ];
            }

            return $rows;
        });
    }

    /**
     * Return the column headings for the export.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'ტექნიკა',
            'ტიპი',
            'ფასი',
            'ინფორმაცია',
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

