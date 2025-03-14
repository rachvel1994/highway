<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkAssetResource\Pages;
use App\Models\WorkAsset;
use Exception;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
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
    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationGroup = 'ობიექტი';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\TextInput::make('street')
                        ->label('ქუჩა')
                        ->unique(ignoreRecord: true)
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('fuel_spend')
                        ->label('მოხმარებული საწვავი')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('traveled_km')
                        ->label('გავლილი კმ')
                        ->maxLength(255),
                ]),
                Forms\Components\Grid::make(3)->schema([
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
                ]),
                Forms\Components\Select::make('item_type_id')
                    ->label('ნივთის ტიპი')
                    ->searchable()
                    ->preload()
                    ->relationship('itemType', 'title'),
                Forms\Components\TextInput::make('item_quantity')
                    ->label('ნივთის რაოდენობა')
                    ->default(0)
                    ->numeric(),
                Forms\Components\Grid::make(3)->schema([
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
                    Forms\Components\TextInput::make('time_spend')
                        ->label('მოხმარებული დრო'),
                ]),
                Forms\Components\Textarea::make('failure')
                    ->label('ცდენა')
                    ->rows(5)
                    ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('street')
                    ->label('ქუჩა')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('equipment.equipment')
                    ->label('ტექნიკა')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('itemType.title')
                    ->label('ნივთის ტიპი')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('item_quantity')
                    ->label('ნივთის რაოდენობა')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('personal.full_name')
                    ->label('პერსონალი')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('company.title')
                    ->label('კომპანია')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('jobType.title')
                    ->label('სამუშაო ტიპი')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('measure.title')
                    ->label('საზომი ერთეული')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('traveled_km')
                    ->label('გავლილი კმ')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('time_spend')
                    ->label('მოხმარებული დრო')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('fuel_spend')
                    ->label('მოხმარებული საწვავი')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('დამატების თარიღი')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('equipment_id')
                    ->label(__('ტექნიკა'))
                    ->preload()
                    ->relationship('equipment', 'equipment'),
                Tables\Filters\SelectFilter::make('item_type_id')
                    ->label(__('ნივთის ტიპი'))
                    ->preload()
                    ->relationship('itemType', 'title'),
                Tables\Filters\SelectFilter::make('personal_id')
                    ->label(__('პერსონალი'))
                    ->preload()
                    ->relationship('personal', 'full_name'),
                Tables\Filters\SelectFilter::make('company_id')
                    ->label(__('კომპანია'))
                    ->preload()
                    ->relationship('company', 'title'),
                Tables\Filters\SelectFilter::make('company_id')
                    ->label(__('კომპანია'))
                    ->preload()
                    ->relationship('company', 'title'),
                Tables\Filters\SelectFilter::make('job_type_id')
                    ->label(__('სამუშაო ტიპი'))
                    ->preload()
                    ->relationship('jobType', 'title'),
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
