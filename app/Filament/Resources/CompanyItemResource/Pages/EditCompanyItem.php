<?php

namespace App\Filament\Resources\CompanyItemResource\Pages;

use App\Filament\Resources\CompanyItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompanyItem extends EditRecord
{
    protected static string $resource = CompanyItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
