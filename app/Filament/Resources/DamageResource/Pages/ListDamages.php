<?php

namespace App\Filament\Resources\DamageResource\Pages;

use App\Filament\Resources\DamageResource;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListDamages extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = DamageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DamageResource\Widgets\DamageTotalExpense::class
        ];
    }
}
