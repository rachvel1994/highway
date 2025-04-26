<?php

namespace App\Filament\Resources;

use App\Exports\FactoryExport;
use App\Filament\Resources\FactoryResource\Pages;
use App\Forms\Components\NumericInput;
use App\Forms\Components\PriceInput;
use App\Models\Factory;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class FactoryResource extends Resource
{
    protected static ?string $model = Factory::class;
    protected static ?string $navigationLabel = 'ქარხანა';

    protected static ?string $breadcrumb = 'ქარხანა';

    protected static ?string $modelLabel = 'ქარხანა';

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('factory')
                    ->label('ქარხანა')
                    ->required(),
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\TextInput::make('craftsman')
                            ->label('მოხელე'),
                        Forms\Components\TextInput::make('damage')
                            ->label('დაზიანება'),
                        Forms\Components\TextInput::make('detail_name')
                            ->label('დეტალი'),
                    ]),
                Forms\Components\Grid::make(5)
                    ->schema([
                        NumericInput::make('quantity')
                            ->label('რაოდენობა')
                            ->afterStateUpdated(fn(Forms\Get $get, Forms\Set $set) => self::calculateTotalPrice($get, $set)),
                        PriceInput::make('detail_price')
                            ->label('დეტალის ფასი')
                            ->afterStateUpdated(fn(Forms\Get $get, Forms\Set $set) => self::calculateTotalPrice($get, $set)),
                        PriceInput::make('craft_price')
                            ->label('ხელობის ფასი')
                            ->afterStateUpdated(fn(Forms\Get $get, Forms\Set $set) => self::calculateTotalPrice($get, $set)),
                        PriceInput::make('additional_expense')
                            ->label('დამატებითი ხარჯი')
                            ->afterStateUpdated(fn(Forms\Get $get, Forms\Set $set) => self::calculateTotalPrice($get, $set)),
                        PriceInput::make('total_price')
                            ->label('ჯამური ფასი'),
                    ]),
                Forms\Components\Textarea::make('comment')
                    ->rows(5)
                    ->columnSpanFull()
                    ->label('კომენტარი'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('factory')
                    ->label('ქარხანა')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('craftsman')
                    ->label('მოხელე')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('damage')
                    ->label('დაზიანება')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('detail_name')
                    ->label('დეტალი')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('რაოდენობა')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('detail_price')
                    ->label('დეტალის ფასი')
                    ->money('GEL')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('craft_price')
                    ->label('ხელობის ფასი')
                    ->money('GEL')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('additional_expense')
                    ->label('დამატებითი ხარჯი')
                    ->money('GEL')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('comment')
                    ->label('კომენტარი')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('დამატების თარიღი')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
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
                        $fileName = 'ქარხანა_' . $record->factory . '.xlsx';
                        return Excel::download(
                            new FactoryExport([$record]), $fileName
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
                            $fileName = 'ქარხანები_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
                            return Excel::download(
                                new FactoryExport($records), $fileName
                            );
                        }),
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
            'index' => Pages\ListFactories::route('/'),
            'create' => Pages\CreateFactory::route('/create'),
            'edit' => Pages\EditFactory::route('/{record}/edit'),
        ];
    }

    private static function calculateTotalPrice(Forms\Get $get, Forms\Set $set): void
    {
        $quantity = (float)$get('quantity') ?: 0;
        $detailPrice = (float)$get('detail_price') ?: 0;
        $craftPrice = (float)$get('craft_price') ?: 0;
        $additionalExpense = (float)$get('additional_expense') ?: 0;

        $set('total_price', $quantity * $detailPrice + $craftPrice + $additionalExpense);
    }
}
