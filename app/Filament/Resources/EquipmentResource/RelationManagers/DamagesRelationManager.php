<?php

namespace App\Filament\Resources\EquipmentResource\RelationManagers;

use App\Filament\Resources\DamageResource;
use Exception;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class DamagesRelationManager extends RelationManager
{
    protected static string $relationship = 'damages';

    protected static ?string $modelLabel = 'დაზიანება';

    protected static ?string $title = 'დაზიანება';

    public function form(Form $form): Form
    {
        return DamageResource::form($form);
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return DamageResource::table($table);
    }
}
