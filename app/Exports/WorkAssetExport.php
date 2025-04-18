<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class WorkAssetExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    private $data;

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
        return collect($this->data)->flatMap(function ($asset) {
            $rows = [];

            // Parent row
            $rows[] = [
                'ქუჩა' => $asset->street,
                'ობიექტის ჯამი' => $asset->grand_total,
                'დაზიანების ჯამი' => $asset->damage_share_total,
                'სამუშაო დასრულებულია' => $asset->is_completed ? 'დიახ' : 'არა',
                'ინფორმაცია' => 'ობიექტი',
            ];

            // Child rows (WorkAssetDetails)
            foreach ($asset->details as $detail) {
                $rows[] = [
                    'ქუჩა' => null,
                    'ობიექტის ჯამი' => null,
                    'დაზიანების ჯამი' => null,
                    'სამუშაო დასრულებულია' => null,
                    'ინფორმაცია' => 'ობიექტის დეტალები',
                    'სამუშაო ტიპი' => $detail->jobType->title,
                    'ტექნიკა' => $detail->equipment->equipment_with_type ?? 'N/A',
                    'მოხმარებული დრო ' => $detail->time_spend,
                    'რეისის რაოდენობა' => $detail->completed_trip ?? 0,
                    'საწვავი' => $detail->fuel->title,
                    'საწვავის ფასი' => money($detail->fuel_price),
                    'მოხმარებული საწვავი' => $detail->fuel_spend,
                    'საწვავის ჯამური ფასი' => money($detail->fuel_total_price),
                    'კომპანია' => $detail->company->company ?? 'N/A',
                    'მასალის დასახელება' => $detail->companyItem->title_with_company ?? 'N/A',
                    'მასალის ფასი' => money($detail->item_price),
                    'მასალის რაოდენობა' => $detail->item_quantity,
                    'მასალის ჯამური ფასი' => money($detail->item_total_price),
                    'მაღაზია' => $detail->store->store ?? 'N/A',
                    'პროდუქცია' => $detail->storeProduct->title_with_store ?? 'N/A',
                    'პროდუქციის ფასი' => money($detail->product_price),
                    'პროდუქციის რაოდენობა' => $detail->product_quantity,
                    'პროდუქციის ჯამური ფასი' => money($detail->product_price_total),
                    'პერსონის' => $detail->personal->full_name ?? 'N/A',
                    'ხელფასი' => money($detail->person_salary),
                    'ხელფასის ტიპი' => [
                            1 => 'თვიური',
                            2 => 'დღიური',
                            3 => 'გამომუშავება',
                            4 => 'საათობრივი',
                        ][$detail->person_salary_type] ?? 'N/A',
                    'პერსონის ხელფასის ჯამი' => money($detail->person_salary_total),
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
            'ქუჩა',
            'ობიექტის ჯამი',
            'დაზიანების ჯამი',
            'სამუშაო დასრულებულია',
            'ინფორმაცია',
            'სამუშაო ტიპი',
            'ტექნიკა',
            'მოხმარებული დრო ',
            'რეისის რაოდენობა',
            'საწვავი',
            'საწვავის ფასი',
            'მოხმარებული საწვავი',
            'საწვავის ჯამური ფასი',
            'კომპანია',
            'მასალის დასახელება',
            'მასალის ფასი',
            'მასალის რაოდენობა',
            'მასალის ჯამური ფასი',
            'მაღაზია',
            'პროდუქცია',
            'პროდუქციის ფასი',
            'პროდუქციის რაოდენობა',
            'პროდუქციის ჯამური ფასი',
            'პერსონის',
            'ხელფასი',
            'ხელფასის ტიპი',
            'პერსონის ხელფასის ჯამი'
        ];
    }

}

