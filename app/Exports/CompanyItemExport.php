<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CompanyItemExport implements FromCollection, WithHeadings, ShouldAutoSize
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
        return collect($this->data)->flatMap(function ($companyItem) {
            $rows = [];

            // Parent row
            $rows[] = [
                'კომპანია' => $companyItem->company->company,
                'სახელი' => $companyItem->title,
                'ფასი' => money($companyItem->price),
                'რაოდენობა' => $companyItem->quantity,
                'ჯამური ფასი' => money($companyItem->total_price),
                'კატეგორია' => $companyItem->category->title,
                'საზომი ერთეული' => $companyItem->measure->title,
                'კომენტარი' => $companyItem->comment,
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
            'კომპანია',
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
