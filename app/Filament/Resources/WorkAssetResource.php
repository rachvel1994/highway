<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkAssetResource\Pages;
use App\Filament\Resources\WorkAssetResource\RelationManagers;
use App\Models\WorkAsset;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WorkAssetResource extends Resource
{
    protected static ?string $model = WorkAsset::class;
    protected static ?string $navigationLabel = 'ობიექტი';

    protected static ?string $breadcrumb = 'ობიექტი';

    protected static ?string $modelLabel = 'ობიექტი';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('street')
                    ->label('ქუჩა')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('equipment_id')
                    ->label('ტექნიკა')
                    ->searchable()
                    ->preload()
                    ->relationship('equipment', 'equipment'),
                Forms\Components\Select::make('personal_id')
                    ->label('პერსონალი')
                    ->searchable()
                    ->preload()
                    ->relationship('personal', 'full_name'),
                Forms\Components\Select::make('company_id')
                    ->label('კომპანია')
                    ->searchable()
                    ->preload()
                    ->relationship('company', 'title'),
                Forms\Components\Select::make('job_type_id')
                    ->label('სამუშაო ტიპი')
                    ->searchable()
                    ->preload()
                    ->relationship('jobType', 'title'),
                Forms\Components\Select::make('measure_id')
                    ->label('საზომი ერთეული')
                    ->searchable()
                    ->preload()
                    ->relationship('measure', 'title'),
                Forms\Components\TextInput::make('traveled_km')
                    ->label('გავლილი კმ')
                    ->maxLength(255),
                Forms\Components\TimePicker::make('time_spend')
                    ->label('მოხმარებული დრო'),
                Forms\Components\TextInput::make('fuel_spend')
                    ->label('მოხმარებული საწვავი')
                    ->maxLength(255),
                Forms\Components\Textarea::make('failure')
                    ->label('ცდენა')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('taken_items')
                    ->label('წაღებული ნივთები')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('comment')
                    ->label('კომენტარი')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('street')
                    ->searchable(),
                Tables\Columns\TextColumn::make('equipment.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('personal.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('company.title')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jobType.title')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('measure.title')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('traveled_km')
                    ->searchable(),
                Tables\Columns\TextColumn::make('time_spend'),
                Tables\Columns\TextColumn::make('fuel_spend')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkAssets::route('/'),
            'create' => Pages\CreateWorkAsset::route('/create'),
            'edit' => Pages\EditWorkAsset::route('/{record}/edit'),
        ];
    }
}
