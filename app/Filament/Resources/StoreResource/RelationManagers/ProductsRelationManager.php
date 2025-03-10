<?php

namespace App\Filament\Resources\StoreResource\RelationManagers;

use App\Filament\Resources\ProductResource;
use Exception;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static ?string $modelLabel = 'პროდუქტი';

    protected static ?string $title = 'პროდუქტი';

    public function form(Form $form): Form
    {
        return ProductResource::form($form);
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return ProductResource::table($table);
    }
}
