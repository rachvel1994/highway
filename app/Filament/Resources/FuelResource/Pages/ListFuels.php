<?php

namespace App\Filament\Resources\FuelResource\Pages;

use App\Filament\Resources\FuelResource;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListFuels extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = FuelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FuelResource\Widgets\FuelStats::class
        ];
    }
}
