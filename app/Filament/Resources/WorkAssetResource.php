<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkAssetResource\Pages;
use App\Models\CompanyItem;
use App\Models\Product;
use App\Models\WorkAsset;
use Exception;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
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
                Forms\Components\TextInput::make('street')
                    ->label('ქუჩა')
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_completed')
                    ->default(0)
                    ->label('სამუშაო დასრულბულია')
                    ->required(),
                Repeater::make('workAssetEquipments')
                    ->relationship('details')
                    ->label('ობიექტის დეტალები')
                    ->schema([
                        Forms\Components\Grid::make(4)->schema([
                            Forms\Components\Select::make('job_type_id')
                                ->label('სამუშაო ტიპი')
                                ->searchable()
                                ->preload()
                                ->relationship('jobType', 'title'),
                            Forms\Components\Select::make('equipment_id')
                                ->label('ტექნიკა')
                                ->searchable()
                                ->preload()
                                ->relationship('equipment', 'equipment'),
                            Forms\Components\TextInput::make('time_spend')
                                ->label('მოხმარებული დრო')
                                ->default(0)
                                ->numeric(),
                            Forms\Components\TextInput::make('completed_trip')
                                ->label('რეისის რაოდენობა')
                                ->default(0)
                                ->numeric(),
                        ]),
                        Forms\Components\Grid::make(4)->schema([
                            Forms\Components\TextInput::make('fuel_spend')
                                ->label('მოხმარებული საწვავი')
                                ->default(0)
                                ->numeric(),
                            Forms\Components\Select::make('company_id')
                                ->label('კომპანია')
                                ->searchable()
                                ->preload()
                                ->relationship('company', 'title')
                                ->reactive()
                                ->afterStateUpdated(fn(callable $set) => $set('company_item_id', null)),
                            Forms\Components\Select::make('company_item_id')
                                ->label('მასალის დასახელება')
                                ->searchable()
                                ->preload()
                                ->options(fn(Forms\Get $get) => $get('company_id')
                                    ? CompanyItem::where('company_id', $get('company_id'))->pluck('title', 'id')->toArray()
                                    : []
                                )
                                ->reactive()
                                ->disabled(fn(Forms\Get $get) => !$get('store_id')),
                            Forms\Components\TextInput::make('company_item_quantity')
                                ->label('მასალის რაოდენობა')
                                ->default(0)
                                ->numeric(),
                        ]),
                        Forms\Components\Grid::make(4)->schema([
                            Forms\Components\Select::make('store_id')
                                ->label('მაღაზიის დასახელება')
                                ->searchable()
                                ->preload()
                                ->relationship('store', 'title')
                                ->reactive()
                                ->afterStateUpdated(fn(callable $set) => $set('store_product_id', null)),
                            Forms\Components\Select::make('store_product_id')
                                ->label('პროდუქცია')
                                ->searchable()
                                ->preload()
                                ->options(fn(Forms\Get $get) => $get('store_id')
                                    ? Product::where('store_id', $get('store_id'))->pluck('title', 'id')->toArray()
                                    : []
                                )
                                ->reactive()
                                ->afterStateUpdated(fn(callable $set, callable $get) => $set('store_product_price', Product::where('id', $get('store_product_id'))->value('price') ?? 0)
                                )
                                ->disabled(fn(Forms\Get $get) => !$get('store_id')),
                            Forms\Components\TextInput::make('store_product_quantity')
                                ->label('პროდუქციის რაოდენობა')
                                ->default(0)
                                ->numeric()
                                ->reactive(),
                            Forms\Components\TextInput::make('store_product_price')
                                ->label('პროდუქციის ფასი')
                                ->default(0)
                                ->prefix('₾')
                                ->numeric(),
                        ]),
                        Forms\Components\Grid::make(5)
                            ->schema([
                                Forms\Components\Select::make('personal_id')
                                    ->label('პერსონალი')
                                    ->searchable()
                                    ->preload()
                                    ->relationship('personal', 'full_name')
                                    ->reactive(),
                                Forms\Components\TextInput::make('person_salary')
                                    ->label('ხელფასი')
                                    ->postfix('₾')
                                    ->default(0)
                                    ->numeric()
                                    ->reactive()
                                    ->afterStateUpdated(fn(callable $set, callable $get) => recalculateSalary($set, $get))
                                    ->visible(fn(Forms\Get $get) => !empty($get('personal_id'))),
                                Forms\Components\Select::make('person_salary_type')
                                    ->label('ხელფასის ტიპი')
                                    ->options([
                                        1 => 'თვიური',
                                        2 => 'დღიური',
                                        3 => 'გამომუშავება',
                                    ])
                                    ->default(1)
                                    ->reactive()
                                    ->afterStateUpdated(fn(callable $set, callable $get) => recalculateSalary($set, $get))
                                    ->visible(fn(callable $get) => !empty($get('personal_id'))),
                                Forms\Components\TextInput::make('person_worked_days')
                                    ->label('ნამუშევარი დღეები')
                                    ->numeric()
                                    ->default(0)
                                    ->reactive()
                                    ->afterStateUpdated(fn(callable $set, callable $get) => recalculateSalary($set, $get))
                                    ->visible(fn(callable $get) => $get('person_salary_type') == 2),
                                Forms\Components\TextInput::make('person_worked_quantity')
                                    ->label('რაოდენობა')
                                    ->numeric()
                                    ->default(0)
                                    ->reactive()
                                    ->afterStateUpdated(fn(callable $set, callable $get) => recalculateSalary($set, $get))
                                    ->visible(fn(callable $get) => $get('person_salary_type') == 3),
                                Forms\Components\TextInput::make('person_salary_total')
                                    ->label('ხელფასის ჯამი')
                                    ->postfix('₾')
                                    ->default(0)
                                    ->numeric()
                                    ->visible(fn(Forms\Get $get) => !empty($get('personal_id'))),
                            ])
                    ])
                    ->orderColumn('created_at')
                    ->collapsed()
                    ->cloneable()
                    ->addActionLabel('ობიექტის დეტალების დამატება')
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
                Tables\Columns\ToggleColumn::make('is_completed')
                    ->label('სამუშაო დასრულბულია')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('დამატების თარიღი')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_completed')
                    ->label('სამუშაო დასრულბულია')
                    ->options([
                        '0' => 'არ არის დასრულებული',
                        '1' => 'დასრულებულია',
                    ]),
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
