<?php

namespace App\Filament\Resources\MeasureResource\Pages;

use App\Filament\Resources\MeasureResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMeasure extends EditRecord
{
    protected static string $resource = MeasureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
