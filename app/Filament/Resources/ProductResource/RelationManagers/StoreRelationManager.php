<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Filament\Resources\StoreResource;
use Exception;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class StoreRelationManager extends RelationManager
{
    protected static string $relationship = 'store';

    protected static ?string $modelLabel = 'მაღაზია';

    protected static ?string $title = 'მაღაზია';

    public function form(Form $form): Form
    {
        return StoreResource::form($form);
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return StoreResource::table($table);
    }
}
