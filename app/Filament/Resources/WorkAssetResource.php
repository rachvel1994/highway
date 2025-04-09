<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkAssetResource\Pages;
use App\Models\WorkAsset;
use Exception;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
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
                Forms\Components\TextInput::make('grand_total')
                    ->label('ობიექტის ჯამი')
                    ->default(0)
                    ->reactive()
                    ->postfix('₾'),
                Forms\Components\Toggle::make('is_completed')
                    ->default(0)
                    ->label('სამუშაო დასრულბულია')
                    ->required(),
                Repeater::make('workAssetEquipments')
                    ->relationship('details')
                    ->label('ობიექტის დეტალები')
                    ->schema([
                        Forms\Components\Select::make('job_type_id')
                            ->label('სამუშაო ტიპი')
                            ->searchable()
                            ->preload()
                            ->relationship('jobType', 'title'),
                        Tabs::make('ობიექტი')
                            ->tabs([
                                Tab::make('ტექნიკა')->schema([
                                    Forms\Components\Grid::make(3)->schema([
                                        Forms\Components\Select::make('equipment_id')
                                            ->label('ტექნიკა')
                                            ->searchable()
                                            ->preload()
                                            ->relationship('equipment', 'equipment'),

                                        Forms\Components\TextInput::make('time_spend')
                                            ->label('მოხმარებული დრო')
                                            ->default(0)
                                            ->minValue(0)
                                            ->maxValue(8)
                                            ->numeric()
                                            ->step('0.01'),

                                        Forms\Components\TextInput::make('completed_trip')
                                            ->label('რეისის რაოდენობა')
                                            ->default(0)
                                            ->numeric(),
                                    ]),
                                ]),

                                Tab::make('საწვავი')->schema([
                                    Forms\Components\Grid::make(4)->schema([

                                        Forms\Components\Select::make('fuel_id')
                                            ->label('საწვავი')
                                            ->searchable()
                                            ->preload()
                                            ->relationship('fuel', 'title')
                                            ->reactive()
                                            ->afterStateUpdated(function (callable $set, callable $get) {
                                                $item = getFuelById($get('fuel_id'));
                                                $price = $item->price ?? 0;
                                                $quantity = $get('fuel_spend') ?? 0;
                                                $set('fuel_price', $price);
                                                $set('fuel_total_price', getTotalPrice($price, $quantity));
                                            }),

                                        Forms\Components\TextInput::make('fuel_price')
                                            ->label('საწვავის ფასი')
                                            ->default(0)
                                            ->minValue(0)
                                            ->numeric()
                                            ->reactive()
                                            ->step('0.01')
                                            ->afterStateUpdated(function (callable $set, callable $get) {
                                                $price = $get('fuel_price') ?? 0;
                                                $quantity = $get('fuel_spend') ?? 0;
                                                $set('fuel_total_price', getTotalPrice($price, $quantity));
                                            }),

                                        Forms\Components\TextInput::make('fuel_spend')
                                            ->label('მოხმარებული საწვავი')
                                            ->default(0)
                                            ->numeric()
                                            ->reactive()
                                            ->minValue(0)
                                            ->step('0.01')
                                            ->afterStateUpdated(function (callable $set, callable $get) {
                                                $price = $get('fuel_price') ?? 0;
                                                $quantity = $get('fuel_spend') ?? 0;
                                                $set('fuel_total_price', getTotalPrice($price, $quantity));
                                            }),

                                        Forms\Components\TextInput::make('fuel_total_price')
                                            ->label('ჯამური ფასი')
                                            ->default(0)
                                            ->minValue(0)
                                            ->numeric()
                                            ->reactive()
                                            ->postfix('₾'),
                                    ]),
                                ]),


                                Tab::make('კომპანია')->schema([
                                    Forms\Components\Grid::make(5)->schema([

                                        Forms\Components\Select::make('company_id')
                                            ->label('კომპანია')
                                            ->searchable()
                                            ->preload()
                                            ->relationship('company', 'company')
                                            ->reactive()
                                            ->afterStateUpdated(fn(callable $set) => $set('item_id', null)),

                                        Forms\Components\Select::make('item_id')
                                            ->label('მასალის დასახელება')
                                            ->searchable()
                                            ->preload()
                                            ->options(fn(Forms\Get $get) => $get('company_id')
                                                ? getItemsByCompanyId($get('company_id'))
                                                : [])
                                            ->reactive()
                                            ->afterStateUpdated(function (callable $set, callable $get) {
                                                $item = getItemById($get('item_id'));
                                                $price = $item->price ?? 0;
                                                $quantity = $get('item_quantity') ?? 0;
                                                $set('item_price', $price);
                                                $set('item_total_price', getTotalPrice($price, $quantity));
                                            })
                                            ->disabled(fn(Forms\Get $get) => !$get('company_id')),

                                        Forms\Components\TextInput::make('item_price')
                                            ->label('მასალის ფასი')
                                            ->default(0)
                                            ->minValue(0)
                                            ->numeric()
                                            ->reactive()
                                            ->afterStateUpdated(function (callable $set, callable $get) {
                                                $price = $get('item_price') ?? 0;
                                                $quantity = $get('item_quantity') ?? 0;
                                                $set('item_total_price', getTotalPrice($price, $quantity));
                                            })
                                            ->step('0.01'),

                                        Forms\Components\TextInput::make('item_quantity')
                                            ->label('მასალის რაოდენობა')
                                            ->default(0)
                                            ->minValue(0)
                                            ->numeric()
                                            ->reactive()
                                            ->afterStateUpdated(function (callable $set, callable $get) {
                                                $price = $get('item_price') ?? 0;
                                                $quantity = $get('item_quantity') ?? 0;
                                                $set('item_total_price', getTotalPrice($price, $quantity));
                                            })
                                            ->step('0.01'),

                                        Forms\Components\TextInput::make('item_total_price')
                                            ->label('ჯამური ფასი')
                                            ->default(0)
                                            ->minValue(0)
                                            ->numeric()
                                            ->reactive()
                                            ->postfix('₾'),
                                    ]),
                                ]),


                                Tab::make('მაღაზია')->schema([
                                    Forms\Components\Grid::make(5)->schema([
                                        Forms\Components\Select::make('store_id')
                                            ->label('მაღაზიის დასახელება')
                                            ->searchable()
                                            ->preload()
                                            ->relationship('store', 'store')
                                            ->reactive()
                                            ->afterStateUpdated(fn(callable $set) => $set('store_product_id', null)),

                                        Forms\Components\Select::make('store_product_id')
                                            ->label('პროდუქცია')
                                            ->searchable()
                                            ->preload()
                                            ->options(fn(Forms\Get $get) => $get('store_id')
                                                ? getProductsByStoreId($get('store_id'))
                                                : []
                                            )
                                            ->reactive()
                                            ->afterStateUpdated(function (callable $set, callable $get) {
                                                $product = getProductById($get('store_product_id'));
                                                $price = $product->price ?? 0;
                                                $quantity = $get('product_quantity') ?? 0;
                                                $set('product_price', $price);
                                                $set('product_price_total', getTotalPrice($price, $quantity));
                                            })
                                            ->disabled(fn(Forms\Get $get) => !$get('store_id')),

                                        Forms\Components\TextInput::make('product_price')
                                            ->label('პროდუქციის ფასი')
                                            ->default(0)
                                            ->minValue(0)
                                            ->postfix('₾')
                                            ->numeric()
                                            ->reactive()
                                            ->afterStateUpdated(function (callable $set, callable $get) {
                                                $price = $get('product_price') ?? 0;
                                                $quantity = $get('product_quantity') ?? 0;
                                                $set('product_price_total', getTotalPrice($price, $quantity));
                                            })
                                            ->step('0.01'),

                                        Forms\Components\TextInput::make('product_quantity')
                                            ->label('პროდუქციის რაოდენობა')
                                            ->default(0)
                                            ->minValue(0)
                                            ->numeric()
                                            ->reactive()
                                            ->afterStateUpdated(function (callable $set, callable $get) {
                                                $price = $get('product_price') ?? 0;
                                                $quantity = $get('product_quantity') ?? 0;
                                                $set('product_price_total', getTotalPrice($price, $quantity));
                                            })
                                            ->step('0.01'),

                                        Forms\Components\TextInput::make('product_price_total')
                                            ->label('ჯამური ფასი')
                                            ->default(0)
                                            ->minValue(0)
                                            ->numeric()
                                            ->reactive()
                                            ->postfix('₾'),
                                    ]),
                                ]),

                                Tab::make('პერსონალი')->schema([
                                    Forms\Components\Grid::make(5)->schema([
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
                                            ->minValue(0)
                                            ->numeric()
                                            ->step('0.01')
                                            ->reactive()
                                            ->afterStateUpdated(fn(callable $set, callable $get) => recalculateSalary($set, $get)),
                                        Forms\Components\Select::make('person_salary_type')
                                            ->label('ხელფასის ტიპი')
                                            ->options([
                                                1 => 'თვიური',
                                                2 => 'დღიური',
                                                3 => 'გამომუშავება',
                                                4 => 'საათობრივი',
                                            ])
                                            ->default(1)
                                            ->reactive()
                                            ->afterStateUpdated(fn(callable $set, callable $get) => recalculateSalary($set, $get)),

                                        Forms\Components\TextInput::make('person_worked_days')
                                            ->label('ნამუშევარი დღეები')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->reactive()
                                            ->afterStateUpdated(fn(callable $set, callable $get) => recalculateSalary($set, $get))
                                            ->visible(fn(Forms\Get $get) => $get('person_salary_type') == 2),

                                        Forms\Components\TextInput::make('person_worked_quantity')
                                            ->label(fn(Forms\Get $get) => $get('person_salary_type') == 3 ? 'რაოდენობა' : 'ნამუშევარი საათი')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->reactive()
                                            ->afterStateUpdated(fn(callable $set, callable $get) => recalculateSalary($set, $get))
                                            ->visible(fn(Forms\Get $get) => in_array($get('person_salary_type'), [3, 4])),

                                        Forms\Components\TextInput::make('person_salary_total')
                                            ->label('ხელფასის ჯამი')
                                            ->postfix('₾')
                                            ->default(0)
                                            ->minValue(0)
                                            ->numeric()
                                            ->reactive()
                                            ->step('0.01'),
                                    ]),
                                ]),
                            ])
                    ])
                    ->afterStateHydrated(fn(Forms\Set $set, Forms\Get $get) => self::grandTotal($set, $get))
                    ->orderColumn('created_at')
                    ->collapsed()
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
                Tables\Columns\TextColumn::make('grand_total')
                    ->label('სრული ჯამი')
                    ->money('GEL')
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
                    ->label('დასრულებული სამუშაო')
                    ->options([
                        1 => 'დასრულებული',
                        0 => 'დაუსრულებელი',
                    ]),

                Tables\Filters\SelectFilter::make('details.equipment_id')
                    ->label('ტექნიკა')
                    ->relationship('details.equipment', 'equipment'),

                Tables\Filters\SelectFilter::make('details.fuel_id')
                    ->label('საწვავი')
                    ->relationship('details.fuel', 'title'),

                Tables\Filters\SelectFilter::make('details.company_id')
                    ->label('კომპანია')
                    ->relationship('details.company', 'company'),

                Tables\Filters\SelectFilter::make('details.item_id')
                    ->label('მასალა')
                    ->relationship('details.companyItem', 'title'),

                Tables\Filters\SelectFilter::make('details.store_id')
                    ->label('მაღაზია')
                    ->relationship('details.store', 'store'),

                Tables\Filters\SelectFilter::make('details.store_product_id')
                    ->label('პროდუქცია')
                    ->relationship('details.storeProduct', 'title'),

                Tables\Filters\SelectFilter::make('details.personal_id')
                    ->label('პერსონალი')
                    ->relationship('details.personal', 'full_name'),

                Tables\Filters\SelectFilter::make('details.job_type_id')
                    ->label('სამუშაო ტიპი')
                    ->relationship('details.jobType', 'title'),
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

    private static function grandTotal(Forms\Set $set, Forms\Get $get): void
    {
        $details = $get('workAssetEquipments') ?? [];
        $total = 0;

        foreach ($details as $row) {
            $total += self::calculateGrandTotal($row);
        }

        $set('grand_total', $total);
    }

    private static function calculateGrandTotal(array $row): float
    {
        // Ensure time is numeric
        $workedTime = is_numeric($row['time_spend']) ? (float)$row['time_spend'] : 0;

        // Fetch equipment
        $equipment = getEquipmentById($row['equipment_id']);

        // Base total
        $total = $row['fuel_total_price'] + $row['item_total_price'] + $row['product_price_total'] + $row['person_salary_total'];

        if ($equipment) {
            if ($equipment->type === 'rent') {
                $total += ($equipment->price / 8) * min($workedTime, 8);
            } else {
                $damageSum = $equipment->damages()?->sum('total_price') ?? 0;
                $total += ($damageSum / 8) * min($workedTime, 8);
            }
        }

        return $total;
    }
}
