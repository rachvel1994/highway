<?php

namespace App\Filament\Resources;

use App\Exports\EquipmentExport;
use App\Filament\Resources\EquipmentResource\Pages;
use App\Filament\Resources\EquipmentResource\RelationManagers\DamagesRelationManager;
use App\Forms\Components\PriceInput;
use App\Models\Equipment;
use Exception;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class EquipmentResource extends Resource
{
    protected static ?string $model = Equipment::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'ტექნიკა';

    protected static ?string $breadcrumb = 'ტექნიკა';

    protected static ?string $modelLabel = 'ტექნიკა';
    protected static ?string $navigationGroup = 'ტექნიკა';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('equipment')
                    ->label('ტექნიკა')
                    ->unique(ignoreRecord: true)
                    ->columnSpanFull()
                    ->required(),
                Forms\Components\Select::make('type')
                    ->label('ტიპი')
                    ->options([
                        'main' => 'საკუთარი',
                        'rent' => 'ნაქირავები',
                    ])
                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                        if ($get('type') == 'main') {
                            $set('price', 0);
                        }
                    })
                    ->required()
                    ->reactive(),
                PriceInput::make('price')
                    ->label('ფასი')
                    ->minValue(0)
                    ->disabled(fn(Forms\Get $get) => $get('type') == 'main'),
            ]);
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('equipment')
                    ->label('ტექნიკა')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('type')
                    ->label('ტიპი')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'main' => 'საკუთარი',
                        'rent' => 'ნაქირავები',
                        default => $state,
                    })
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('price')
                    ->label('ფასი')
                    ->searchable()
                    ->sortable()
                    ->suffix(' ₾')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('დამატების თარიღი')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('ტიპი')
                    ->options([
                        'main' => 'საკუთარი',
                        'rent' => 'ნაქირავები',
                    ]),
                Tables\Filters\Filter::make('price')
                    ->form([
                        PriceInput::make('from')
                            ->label('მინ. ფასი'),
                        PriceInput::make('to')
                            ->label('მაქს. ფასი'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'], function (Builder $query, ?string $from) {
                                $query->where('price', '>=', $from * 100);
                            })
                            ->when($data['to'], function (Builder $query, ?string $to) {
                                $query->where('price', '<=', $to * 100);
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
                        $record->load('damages');

                        $fileName = 'ტექნიკა_' . $record->equipment . '.xlsx';
                        return Excel::download(
                            new EquipmentExport([$record]), $fileName
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
                            foreach ($records as $record) {
                                $record->load('damages');
                            }

                            $fileName = 'ტექნიკები_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
                            return Excel::download(
                                new EquipmentExport($records), $fileName
                            );
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            DamagesRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEquipment::route('/'),
            'create' => Pages\CreateEquipment::route('/create'),
            'edit' => Pages\EditEquipment::route('/{record}/edit'),
        ];
    }
}
