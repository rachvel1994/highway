<?php

namespace App\Filament\Resources\WorkAssetResource\Pages;

use App\Filament\Resources\WorkAssetResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWorkAsset extends EditRecord
{
    protected static string $resource = WorkAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
