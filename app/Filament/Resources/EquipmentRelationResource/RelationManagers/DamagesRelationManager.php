<?php

namespace App\Filament\Resources\EquipmentRelationResource\RelationManagers;

use App\Filament\Resources\DamageResource;
use Exception;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DamagesRelationManager extends RelationManager
{
    protected static string $relationship = 'damages';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('equipment_id')
                    ->label('ტექნიკა')
                    ->searchable()
                    ->preload()
                    ->relationship('equipment', 'equipment')
                    ->required(),
                Forms\Components\TextInput::make('craftsman')
                    ->label('მოხელე'),
                Forms\Components\TextInput::make('damage')
                    ->label('დაზიანება'),
                Forms\Components\TextInput::make('detail_name')
                    ->label('დეტალი'),
                Forms\Components\Grid::make(4)
                    ->schema([
                        Forms\Components\TextInput::make('quantity')
                            ->label('რაოდენობა')
                            ->numeric(),
                        Forms\Components\TextInput::make('detail_price')
                            ->numeric()
                            ->label('დეტალის ფასი')
                            ->postfix('₾'),
                        Forms\Components\TextInput::make('craft_price')
                            ->numeric()
                            ->label('ხელობის ფასი')
                            ->postfix('₾'),
                        Forms\Components\TextInput::make('additional_expense')
                            ->numeric()
                            ->label('დამატებითი ხარჯი')
                            ->postfix('₾'),
                    ]),
                Forms\Components\Textarea::make('comment')
                    ->rows(5)
                    ->columnSpanFull()
                    ->label('კომენტარი'),
            ]);
    }

    /**
     * @throws Exception
     */
    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('equipment_id')
                    ->label('ტექნიკა')
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
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static ?string $modelLabel = 'დაზიანება';

    protected static ?string $title = 'დაზიანება';
}
