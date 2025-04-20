<?php

namespace App;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductExport implements FromCollection, WithHeadings, ShouldAutoSize
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
        return collect($this->data)->flatMap(function ($product) {
            $rows = [];

            // Parent row
            $rows[] = [
                'მაღაზია' => $product->store->store,
                'სახელი' => $product->title,
                'ფასი' => money($product->price),
                'რაოდენობა' => $product->quantity,
                'ჯამური ფასი' => money($product->total_price),
                'კატეგორია' => $product->category->title,
                'საზომი ერთეული' => $product->measure->title,
                'კომენტარი' => $product->comment,
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
            'მაღაზია',
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
