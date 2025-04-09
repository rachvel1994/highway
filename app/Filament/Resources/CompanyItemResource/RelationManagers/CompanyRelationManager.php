<?php

namespace App\Filament\Resources\CompanyItemResource\RelationManagers;

use App\Filament\Resources\CompanyResource;
use Exception;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class CompanyRelationManager extends RelationManager
{
    protected static string $relationship = 'company';

    protected static ?string $modelLabel = 'კომპანია';

    protected static ?string $title = 'კომპანია';

    public function form(Form $form): Form
    {
        return CompanyResource::form($form);
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return CompanyResource::table($table);
    }
}
