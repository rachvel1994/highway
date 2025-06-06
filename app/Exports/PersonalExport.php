<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PersonalExport implements FromCollection, WithHeadings, ShouldAutoSize
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
        return collect($this->data)->flatMap(function ($person) {
            $rows = [];

            // Parent row
            $rows[] = [
                'სახელი, გვარი' => $person->full_name,
                'ინფორმაცია' => 'პერსონა',
            ];

            foreach ($person->workAssetDetails as $workAssetDetail) {
                $rows[] = [
                    'სახელი, გვარი' => null,
                    'ინფორმაცია' => 'ხელფასები',
                    'ობიექტი' => $workAssetDetail->workAsset->street,
                    'ხელფასი' => money($workAssetDetail->person_salary_total),
                ];
            }

            return $rows;
        });
    }

    /**
     * Return the column headings for the export.
     */
    public function headings(): array
    {
        return [
            'სახელი, გვარი',
            'ინფორმაცია',
            'ობიექტი',
            'ხელფასი',
        ];
    }
}
