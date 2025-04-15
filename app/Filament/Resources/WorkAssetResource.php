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
use Livewire\Component;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

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
                    Forms\Components\TextInput::make('grand_total')
                        ->label('ობიექტის ჯამი')
                        ->default(0)
                        ->disabled()
                        ->postfix('₾'),
                    Forms\Components\TextInput::make('damage_share_total')
                        ->label('დაზიანების ჯამი')
                        ->default(0)
                        ->disabled()
                        ->postfix('₾'),
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
                                            ->numeric(),

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
                                                $set('fuel_price', $price);
                                                self::getFuelTotalPrice($set, $get);
                                            }),

                                        Forms\Components\TextInput::make('fuel_price')
                                            ->label('საწვავის ფასი')
                                            ->default(0)
                                            ->minValue(0)
                                            ->numeric()
                                            ->reactive()
                                            ->afterStateUpdated(fn(Forms\Set $set, Forms\Get $get) => self::getFuelTotalPrice($set, $get)),

                                        Forms\Components\TextInput::make('fuel_spend')
                                            ->label('მოხმარებული საწვავი')
                                            ->default(0)
                                            ->numeric()
                                            ->reactive()
                                            ->minValue(0)
                                            ->afterStateUpdated(fn(Forms\Set $set, Forms\Get $get) => self::getFuelTotalPrice($set, $get)),

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
                                                $set('item_price', $price);
                                                self::getItemTotalPrice($set, $get);
                                            })
                                            ->disabled(fn(Forms\Get $get) => !$get('company_id')),

                                        Forms\Components\TextInput::make('item_price')
                                            ->label('მასალის ფასი')
                                            ->default(0)
                                            ->minValue(0)
                                            ->numeric()
                                            ->reactive()
                                            ->afterStateUpdated(fn(callable $set, callable $get) => self::getItemTotalPrice($set, $get)),

                                        Forms\Components\TextInput::make('item_quantity')
                                            ->label('მასალის რაოდენობა')
                                            ->default(0)
                                            ->minValue(0)
                                            ->numeric()
                                            ->reactive()
                                            ->afterStateUpdated(fn(callable $set, callable $get) => self::getItemTotalPrice($set, $get)),

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
                                                $set('product_price', $price);
                                                self::getProductTotalPrice($set, $get);
                                            })
                                            ->disabled(fn(Forms\Get $get) => !$get('store_id')),

                                        Forms\Components\TextInput::make('product_price')
                                            ->label('პროდუქციის ფასი')
                                            ->default(0)
                                            ->minValue(0)
                                            ->postfix('₾')
                                            ->numeric()
                                            ->reactive()
                                            ->afterStateUpdated(fn(callable $set, callable $get) => self::getProductTotalPrice($set, $get)),

                                        Forms\Components\TextInput::make('product_quantity')
                                            ->label('პროდუქციის რაოდენობა')
                                            ->default(0)
                                            ->minValue(0)
                                            ->numeric()
                                            ->reactive()
                                            ->afterStateUpdated(fn(callable $set, callable $get) => self::getProductTotalPrice($set, $get)),

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
                                            ->reactive(),
                                    ]),
                                ]),
                            ])
                    ])
                    ->orderColumn('created_at')
                    ->collapsed()
                    ->addActionLabel('ობიექტის დეტალების დამატება')
                    ->itemLabel(fn(array $state): ?string => self::setRepeaterLabel($state) ?? null)
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
                        DatePicker::make('min_total')
                            ->label('მინ. სრული ჯამი')
                            ->debounce(),
                        DatePicker::make('max_total')
                            ->label('მაქს. სრული ჯამი')
                            ->debounce(),
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
                        DatePicker::make('min')
                            ->label('მინ. თანხა')
                            ->debounce(),
                        DatePicker::make('max')
                            ->label('მაქს. თანხა')
                            ->debounce(),
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
                ExportAction::make()->label('ექსელის ექსპორტი')->modelLabel('dd')->exports([
                    ExcelExport::make('table')->fromTable()->label('მთავარი გვერდის ექსპორტი'),
                    ExcelExport::make('form')->fromForm()->label('შიდა გვერდის ექსპორტი'),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make()->label('ექსპორტი ექსელში')
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

    private static function setRepeaterLabel(array $data): string
    {
        $labels = [];

        if (!empty($data['equipment_id'])) {
            $labels[] = getEquipmentById($data['equipment_id'])->equipment ?? null;
        }

        if (!empty($data['fuel_id'])) {
            $labels[] = getFuelById($data['fuel_id'])->title ?? null;
        }

        if (!empty($data['company_id'])) {
            $labels[] = getCompanyById($data['company_id'])->company ?? null;
        }

        if (!empty($data['store_id'])) {
            $labels[] = getStoreById($data['store_id'])->store ?? null;
        }

        if (!empty($data['personal_id'])) {
            $labels[] = getPersonById($data['personal_id'])->full_name ?? null;
        }

        // Filter nulls and duplicates, return comma-separated string
        return implode(', ', array_filter(array_unique($labels)));
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
