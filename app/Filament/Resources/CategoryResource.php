<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationLabel = 'კატეგორია';

    protected static ?string $breadcrumb = 'კატეგორია';

    protected static ?string $modelLabel = 'კატეგორია';
    protected static ?string $navigationIcon = 'heroicon-o-bars-4';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('სახელი')
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type_id')
                    ->label('კატეგორიის ტიპი')
                    ->options([
                        1 => 'პროდუქტი',
                        2 => 'ნივთი',
                    ])
                    ->required()
            ]);
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('სახელი')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('type_id')
                    ->label('კატეგორიის ტიპი')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn($state) => match ($state) {
                        1 => 'პროდუქტი',
                        2 => 'ნივთი',
                        default => $state,
                    })
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('დამატების თარიღი')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type_id')
                    ->label('კატეგორიის ტიპი')
                    ->options([
                        1 => 'პროდუქტი',
                        2 => 'ნივთი',
                    ])
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                ExportAction::make()->label('ექსელის ექსპორტი')->modelLabel('dd')->exports([
                    ExcelExport::make('table')->fromTable()->label('მთავარი გვერდის ექსპორტი'),
                    ExcelExport::make('form')->fromForm()->label('შიდა გვერდის ექსპორტი'),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()->label('ექსპორტი ექსელში')
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
