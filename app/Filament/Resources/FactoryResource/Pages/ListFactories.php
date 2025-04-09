<?php

namespace App\Filament\Resources\FactoryResource\Pages;

use App\Filament\Resources\FactoryResource;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListFactories extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = FactoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FactoryResource\Widgets\FactoryTotalExpense::class
        ];
    }
}
