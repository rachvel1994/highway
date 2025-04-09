<?php

namespace App\Filament\Resources\CompanyItemResource\Pages;

use App\Filament\Resources\CompanyItemResource;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListCompanyItem extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = CompanyItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
           CompanyItemResource\Widgets\CompanyItemTotalExpense::class
        ];
    }
}
