<?php

namespace App\Filament\Resources\DamageResource\RelationManagers;

use App\Filament\Resources\EquipmentResource;
use Exception;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class EquipmentRelationManager extends RelationManager
{
    protected static string $relationship = 'equipment';

    protected static ?string $modelLabel = 'ტექნიკა';

    protected static ?string $title = 'ტექნიკა';

    public function form(Form $form): Form
    {
        return EquipmentResource::form($form);
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return EquipmentResource::table($table);
    }
}
