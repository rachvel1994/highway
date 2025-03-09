<?php

namespace App\Filament\Resources\PersonalResource\Pages;

use App\Filament\Resources\PersonalResource;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListPersonal extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = PersonalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PersonalResource\Widgets\PersonalTotalExpense::class
        ];
    }
}
