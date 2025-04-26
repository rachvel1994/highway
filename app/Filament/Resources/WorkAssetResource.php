<?php

namespace App\Filament\Resources;

use App\Exports\WorkAssetExport;
use App\Filament\Resources\WorkAssetResource\Pages;
use App\Forms\Components\NumericInput;
use App\Forms\Components\PriceInput;
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
use Maatwebsite\Excel\Facades\Excel;

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
                    PriceInput::make('grand_total')
                        ->label('ობიექტის ჯამი'),
                    PriceInput::make('damage_share_total')
                        ->label('დაზიანების ჯამი'),
                ]),
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
                            ->required()
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
                                            ->relationship('equipment', 'id')
                                            ->getOptionLabelFromRecordUsing(fn($record) => $record->equipment_with_type),

                                        NumericInput::make('time_spend')
                                            ->label('მოხმარებული დრო'),

                                        NumericInput::make('completed_trip')
                                            ->label('რეისის რაოდენობა'),
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
                                                $set('fuel_price', $price);
                                                self::getFuelTotalPrice($set, $get);
                                            }),

                                        PriceInput::make('fuel_price')
                                            ->label('საწვავის ფასი')
                                            ->afterStateUpdated(fn(Forms\Set $set, Forms\Get $get) => self::getFuelTotalPrice($set, $get)),

                                        NumericInput::make('fuel_spend')
                                            ->label('მოხმარებული საწვავი')
                                            ->afterStateUpdated(fn(Forms\Set $set, Forms\Get $get) => self::getFuelTotalPrice($set, $get)),

                                        PriceInput::make('fuel_total_price')
                                            ->label('ჯამური ფასი'),
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
                                                $set('item_price', $price);
                                                self::getItemTotalPrice($set, $get);
                                            })
                                            ->disabled(fn(Forms\Get $get) => !$get('company_id')),

                                        PriceInput::make('item_price')
                                            ->label('მასალის ფასი')
                                            ->afterStateUpdated(fn(callable $set, callable $get) => self::getItemTotalPrice($set, $get)),

                                        NumericInput::make('item_quantity')
                                            ->label('მასალის რაოდენობა')
                                            ->afterStateUpdated(fn(callable $set, callable $get) => self::getItemTotalPrice($set, $get)),

                                        PriceInput::make('item_total_price')
                                            ->label('ჯამური ფასი'),
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
                                                $set('product_price', $price);
                                                self::getProductTotalPrice($set, $get);
                                            })
                                            ->disabled(fn(Forms\Get $get) => !$get('store_id')),

                                        PriceInput::make('product_price')
                                            ->label('პროდუქციის ფასი')
                                            ->afterStateUpdated(fn(callable $set, callable $get) => self::getProductTotalPrice($set, $get)),

                                        NumericInput::make('product_quantity')
                                            ->label('პროდუქციის რაოდენობა')
                                            ->afterStateUpdated(fn(callable $set, callable $get) => self::getProductTotalPrice($set, $get)),

                                        PriceInput::make('product_price_total')
                                            ->label('ჯამური ფასი'),
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
                                        PriceInput::make('person_salary')
                                            ->label('ხელფასი')
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

                                        NumericInput::make('person_worked_days')
                                            ->label('ნამუშევარი დღეები')
                                            ->afterStateUpdated(fn(callable $set, callable $get) => recalculateSalary($set, $get))
                                            ->visible(fn(Forms\Get $get) => $get('person_salary_type') == 2),

                                        NumericInput::make('person_worked_quantity')
                                            ->label(fn(Forms\Get $get) => $get('person_salary_type') == 3 ? 'რაოდენობა' : 'ნამუშევარი საათი')
                                            ->afterStateUpdated(fn(callable $set, callable $get) => recalculateSalary($set, $get))
                                            ->visible(fn(Forms\Get $get) => in_array($get('person_salary_type'), [3, 4])),

                                        PriceInput::make('person_salary_total')
                                            ->label('ხელფასის ჯამი'),
                                    ]),
                                ]),
                            ])
                    ])
                    ->orderColumn('created_at')
                    ->collapsed()
                    ->addActionLabel('ობიექტის დეტალების დამატება')
                    ->itemLabel(fn(array $state, Forms\Get $get): ?string => self::setRepeaterLabel($state, $get) ?? null)
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

                Tables\Filters\Filter::make('grand_total')
                    ->form([
                        PriceInput::make('min_total')
                            ->label('მინ. სრული ჯამი'),
                        PriceInput::make('max_total')
                            ->label('მაქს. სრული ჯამი'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['min_total'], function (Builder $query, ?string $minTotal) {
                                $query->where('grand_total', '>=', $minTotal);
                            })
                            ->when($data['max_total'], function (Builder $query, ?string $maxTotal) {
                                $query->where('grand_total', '<=', $maxTotal);
                            });
                    }),
                Tables\Filters\Filter::make('money')
                    ->form([
                        PriceInput::make('min')
                            ->label('მინ. თანხა'),
                        PriceInput::make('max')
                            ->label('მაქს. თანხა'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['min'], function (Builder $query, ?string $min) {
                                $query->where('fuel_price', '>=', $min)
                                    ->orWhere('fuel_total_price', '>=', $min)
                                    ->orWhere('item_price', '>=', $min)
                                    ->orWhere('item_total_price', '>=', $min)
                                    ->orWhere('product_price', '>=', $min)
                                    ->orWhere('product_price_total', '>=', $min)
                                    ->orWhere('person_salary_total', '>=', $min);
                            })
                            ->when($data['max'], function (Builder $query, ?string $max) {
                                $query->where('fuel_price', '<=', $max)
                                    ->orWhere('fuel_total_price', '<=', $max)
                                    ->orWhere('item_price', '<=', $max)
                                    ->orWhere('item_total_price', '<=', $max)
                                    ->orWhere('product_price', '<=', $max)
                                    ->orWhere('product_price_total', '<=', $max)
                                    ->orWhere('person_salary_total', '<=', $max);
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
                        $record->load('details');

                        $fileName = 'ობიექტი_' . $record->street . '.xlsx';
                        return Excel::download(
                            new WorkAssetExport([$record]), $fileName
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
                                $record->load('details');
                            }

                            $fileName = 'ობიექტები_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
                            return Excel::download(
                                new WorkAssetExport($records), $fileName
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
            'index' => Pages\ListWorkAssets::route('/'),
            'create' => Pages\CreateWorkAsset::route('/create'),
            'edit' => Pages\EditWorkAsset::route('/{record}/edit'),
        ];
    }

    private static function setRepeaterLabel(array $data, Forms\Get $get): string
    {
        $labels = [];
        $total = 0;
        $isCompleted = (bool)($get('is_completed') ?? false);

        if (!empty($data['equipment_id'])) {
            $equipment = getEquipmentById($data['equipment_id']);
            $labels[] = $equipment->equipment ?? null;

            if ($equipment) {
                if ($equipment->type === 'rent') {
                    if (!$isCompleted) {
                        $total += ($equipment->price / 8) * min($data['time_spend'], 8);
                    } else {
                        $total += (float)$get('total');
                    }
                } else {
                    $totalDamage = (float)optional($equipment->damages())->sum('total_price');
                    $totalTimeUsed = (float)optional($equipment->workAssetDetails())->sum('time_spend');
                    $localDamageCount = optional($equipment->damages())->count();

                    if (!$isCompleted && $totalTimeUsed > 0 && !empty($data['time_spend'])) {
                        $shareRatio = $data['time_spend'] / $totalTimeUsed;
                        $damageShare = $totalDamage * $shareRatio;
                        $total += $damageShare;
                    } elseif ($isCompleted) {
                        $damageShare = (float)$get('damage_share_total');
                        $total += $damageShare;
                    }

                    if ($localDamageCount > 0) {
                        $labels[] = "დაზიანება: {$localDamageCount}";
                    }
                }
            }
        }


        if (!empty($data['fuel_id'])) {
            $labels[] = getFuelById($data['fuel_id'])->title ?? null;
        }

        if (!empty($data['company_id']) && !empty($data['item_id'])) {
            $item = getItemById($data['item_id']);
            if ($item) {
                $labels[] = $item->title;
            }
        }

        if (!empty($data['store_id']) && !empty($data['store_product_id'])) {
            $product = getProductById($data['store_product_id']);
            if ($product) {
                $labels[] = $product->title;
            }
        }

        if (!empty($data['personal_id'])) {
            $person = getPersonById($data['personal_id']);
            if ($person) {
                $labels[] = $person->full_name;
            }
        }

        // Sum all the totals
        $totals = [
            $total,
            $data['fuel_total_price'] ?? 0,
            $data['item_total_price'] ?? 0,
            $data['product_price_total'] ?? 0,
            $data['person_salary_total'] ?? 0,
        ];

        $sum = array_sum(array_map('floatval', $totals));
        $sumFormatted = number_format($sum, 2);

        $labelText = implode(' | ', array_filter($labels));

        return trim($labelText . ($sum > 0 ? " • ჯამი: {$sumFormatted} ₾" : ''));
    }


    private static function getProductTotalPrice(Forms\Set $set, Forms\Get $get): void
    {
        $set('product_price_total', getTotalPrice($get('product_price'), $get('product_quantity')));
    }

    private static function getItemTotalPrice(Forms\Set $set, Forms\Get $get): void
    {
        $set('item_total_price', getTotalPrice($get('item_price'), $get('item_quantity')));
    }

    private static function getFuelTotalPrice(Forms\Set $set, Forms\Get $get): void
    {
        $set('fuel_total_price', getTotalPrice($get('fuel_price'), $get('fuel_spend')));
    }
}
