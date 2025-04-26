<?php

namespace App\Filament\Resources;

use App\Exports\ProductExport;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers\StoreRelationManager;
use App\Forms\Components\NumericInput;
use App\Forms\Components\PriceInput;
use App\Models\Product;
use Exception;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationLabel = 'პროდუქტი';

    protected static ?string $breadcrumb = 'პროდუქტი';

    protected static ?string $modelLabel = 'პროდუქტი';
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'მაღაზია';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('store_id')
                    ->label('მაღაზია')
                    ->preload()
                    ->relationship('store', 'store')
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->label('სახელი')
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->maxLength(255),
                Forms\Components\Grid::make(5)->schema([
                    PriceInput::make('price')
                        ->label('ფასი')
                        ->afterStateUpdated(fn(Forms\Get $get, Forms\Set $set) => self::calculateTotalPrice($get, $set)),
                   NumericInput::make('quantity')
                        ->label('რაოდენობა')
                        ->afterStateUpdated(fn(Forms\Get $get, Forms\Set $set) => self::calculateTotalPrice($get, $set)),
                    PriceInput::make('total_price')
                        ->label('ჯამური ფასი'),
                    Forms\Components\Select::make('category_id')
                        ->label('კატეგორია')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->relationship('category', 'title'),
                    Forms\Components\Select::make('measure_id')
                        ->label('საზომი ერთეული')
                        ->searchable()
                        ->required()
                        ->preload()
                        ->relationship('measure', 'short_title'),
                ]),
                Forms\Components\Textarea::make('comment')
                    ->label('კომენტარი')
                    ->rows(5)
                    ->columnSpanFull(),
            ]);
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('store.store')
                    ->label('მაღაზია')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('title')
                    ->label('სახელი')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('price')
                    ->label('ფასი')
                    ->money('GEL')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('ჯამური ფასი')
                    ->money('GEL')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('რაოდენობა')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('category.title')
                    ->label('კატეგორია')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('measure.short_title')
                    ->label('საზომი ერთეული')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('comment')
                    ->label('კომენტარი')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('დამატების თარიღი')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('store_id')
                    ->label(__('მაღაზია'))
                    ->preload()
                    ->relationship('store', 'store'),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label(__('კატეგორია'))
                    ->preload()
                    ->relationship('category', 'title'),
                Tables\Filters\SelectFilter::make('measure_id')
                    ->label(__('საზომი ერთეული'))
                    ->preload()
                    ->relationship('measure', 'short_title'),
                Tables\Filters\Filter::make('price')
                    ->form([
                        PriceInput::make('min_price')
                            ->label('მინ. ფასი'),
                        PriceInput::make('max_price')
                            ->label('მაქს. ფასი'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['min_price'], function (Builder $query, ?string $from) {
                                $query->where('price', '>=', $from)
                                    ->orWhere('total_price', '>=', $from);
                            })
                            ->when($data['max_price'], function (Builder $query, ?string $to) {
                                $query->where('price', '<=', $to)
                                    ->orWhere('total_price', '<=', $to);
                            });
                    }),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')
                            ->label('თარიღიდან')
                            ->debounce(),
                        DatePicker::make('to')
                            ->label('თარიღამდე')
                            ->debounce(),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'], function (Builder $query, ?string $from) {
                                $query->where('created_at', '>=', $from);
                            })
                            ->when($data['to'], function (Builder $query, ?string $to) {
                                $query->where('created_at', '<=', $to);
                            });
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('export_details')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->label('ექსელის ექსპორტი')
                    ->action(function ($record) {
                        $fileName = 'პროდუქტი_' . $record->title . '.xlsx';
                        return Excel::download(
                            new ProductExport([$record]), $fileName
                        );
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('export_bulk')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->label('ექსპორტი ექსელში')
                        ->action(function ($records) {
                            $fileName = 'პროდუქცია_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
                            return Excel::download(
                                new ProductExport($records), $fileName
                            );
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            StoreRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    private static function calculateTotalPrice(Forms\Get $get, Forms\Set $set): void
    {
        $set('total_price', getTotalPrice($get('price'), $get('quantity')));
    }
}
