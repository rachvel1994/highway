<?php

namespace App\Filament\Resources;

use App\Exports\FuelExport;
use App\Filament\Resources\FuelResource\Pages;
use App\Models\Fuel;
use Exception;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class FuelResource extends Resource
{
    protected static ?string $model = Fuel::class;

    protected static ?string $navigationIcon = 'heroicon-o-fire';

    protected static ?string $navigationLabel = 'საწვავი';

    protected static ?string $breadcrumb = 'საწვავი';

    protected static ?string $modelLabel = 'საწვავი';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('დასახელება')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Grid::make(4)
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->label('ფასი')
                            ->default(0)
                            ->postfix('₾')
                            ->reactive()
                            ->debounce(3)
                            ->afterStateUpdated(fn(Forms\Get $get, Forms\Set $set) => self::calculateTotalPrice($get, $set)),
                        Forms\Components\TextInput::make('quantity')
                            ->label('რაოდენობა')
                            ->numeric()
                            ->default(0)
                            ->reactive()
                            ->debounce(3)
                            ->afterStateUpdated(fn(Forms\Get $get, Forms\Set $set) => self::calculateTotalPrice($get, $set)),
                        Forms\Components\TextInput::make('remain')
                            ->label('ნაშთი')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('total_price')
                            ->label('ჯამური ჯამი')
                            ->reactive()
                            ->default(0)
                    ])
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
                    ->label('დასახელება')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('price')
                    ->label('ფასი')
                    ->money('GEL')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('ჯამური ფასი')
                    ->money('GEL')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('რაოდენობა')
                    ->numeric()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('remain')
                    ->label('ნაშთი')
                    ->numeric()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('დამატების თარიღი')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('price')
                    ->form([
                        TextInput::make('from')
                            ->label('ფასიდან')
                            ->numeric()
                            ->debounce(),
                        TextInput::make('to')
                            ->label('ფასამდე')
                            ->numeric()
                            ->debounce(),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'], function (Builder $query, ?string $from) {
                                $query->where('price', '>=', $from);
                            })
                            ->when($data['to'], function (Builder $query, ?string $to) {
                                $query->where('price', '<=', $to);
                            });
                    }),
                Tables\Filters\Filter::make('total_price')
                    ->form([
                        TextInput::make('total_from')
                            ->label('ჯამური ფასიდან')
                            ->numeric()
                            ->debounce(),
                        TextInput::make('total_to')
                            ->label('ჯამური ფასამდე')
                            ->numeric()
                            ->debounce(),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['total_from'], function (Builder $query, ?string $totalFrom) {
                                $query->where('total_price', '>=', $totalFrom);
                            })
                            ->when($data['total_to'], function (Builder $query, ?string $totalTo) {
                                $query->where('total_price', '<=', $totalTo);
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
                        $fileName = 'საწვავი_' . $record->title . '.xlsx';
                        return Excel::download(
                            new FuelExport([$record]), $fileName
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
                            $fileName = 'საწვავები_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
                            return Excel::download(
                                new FuelExport($records), $fileName
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
            'index' => Pages\ListFuels::route('/'),
            'create' => Pages\CreateFuel::route('/create'),
            'edit' => Pages\EditFuel::route('/{record}/edit'),
        ];
    }

    private static function calculateTotalPrice(Forms\Get $get, Forms\Set $set): void
    {
        $set('total_price', getTotalPrice($get('price'), $get('quantity')));
    }
}
