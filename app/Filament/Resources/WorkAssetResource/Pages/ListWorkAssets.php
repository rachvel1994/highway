<?php

namespace App\Filament\Resources\WorkAssetResource\Pages;

use App\Filament\Resources\WorkAssetResource;
use Filament\Actions;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ListRecords;

class ListWorkAssets extends ListRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = WorkAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            WorkAssetResource\Widgets\WorkAssetStats::class
        ];
    }
}
