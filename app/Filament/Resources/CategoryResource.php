<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

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
                        3 => 'ობიექტი',
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
                        3 => 'ობიექტი',
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
                        3 => 'ობიექტი',
                    ])
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
