<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class CompanyExport implements FromCollection, WithHeadings, ShouldAutoSize
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
        return collect($this->data)->flatMap(function ($company) {
            $rows = [];

            $rows[] = [
                'კომპანია' => $company->company,
                'ჯამური ჯამი' => money($company->total_price),
                'ინფორმაცია' => 'კომპანია',
            ];

            foreach ($company->company_items as $item) {
                $rows[] = [
                    'კომპანია' => null,
                    'ჯამური ჯამი' => null,
                    'ინფორმაცია' => 'ნივთი',
                    'სახელი' => $item->title,
                    'ფასი' => money($item->price),
                    'რაოდენობა' => $item->quantity,
                    'ჯამური ფასი' => money($item->total_price),
                    'კატეგორია' => $item->category->title,
                    'საზომი ერთეული' => $item->measure->title,
                    'კომენტარი' => $item->comment,
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
            'კომპანია',
            'ჯამური ჯამი',
            'ინფორმაცია',
            'სახელი',
            'ფასი',
            'რაოდენობა',
            'ჯამური ფასი',
            'კატეგორია',
            'საზომი ერთეული',
            'კომენტარი',
        ];
    }

}

