<?php

namespace App\Filament\Resources\CompanyItemResource\Pages;

use App\Filament\Resources\CompanyItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompanyItem extends ListRecords
{
    protected static string $resource = CompanyItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
