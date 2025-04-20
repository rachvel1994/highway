<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class StoreExport implements FromCollection, WithHeadings, ShouldAutoSize
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
        return collect($this->data)->flatMap(function ($store) {
            $rows = [];

            $rows[] = [
                'მაღაზია' => $store->store,
                'ინფორმაცია' => 'მაღაზია',
            ];

            foreach ($store->products as $product) {
                $rows[] = [
                    'მაღაზია' => null,
                    'ინფორმაცია' => 'პროდუქტი',
                    'სახელი' => $product->title,
                    'ფასი' => money($product->price),
                    'რაოდენობა' => $product->quantity,
                    'ჯამური ფასი' => money($product->total_price),
                    'კატეგორია' => $product->category->title,
                    'საზომი ერთეული' => $product->measure->title,
                    'კომენტარი' => $product->comment,
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
            'მაღაზია',
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

