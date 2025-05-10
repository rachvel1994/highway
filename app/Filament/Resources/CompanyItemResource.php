<?php

namespace App\Filament\Resources;

use App\Exports\CompanyItemExport;
use App\Filament\Resources\CompanyItemResource\Pages;
use App\Filament\Resources\CompanyItemResource\RelationManagers\CompanyRelationManager;
use App\Forms\Components\NumericInput;
use App\Forms\Components\PriceInput;
use App\Models\CompanyItem;
use Exception;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Unique;
use Maatwebsite\Excel\Facades\Excel;

class CompanyItemResource extends Resource
{
    protected static ?string $model = CompanyItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'ნივთი';

    protected static ?string $breadcrumb = 'ნივთი';

    protected static ?string $modelLabel = 'ნივთი';
    protected static ?string $navigationGroup = 'კომპანია';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\Select::make('company_id')
                        ->label('კომპანია')
                        ->preload()
                        ->relationship('company', 'company')
                        ->required(),
                    Forms\Components\TextInput::make('title')
                        ->label('სახელი')
                        ->required()
//                    ->unique(
//                        table: 'company_items',
//                        column: 'title',
//                        ignoreRecord: true,
//                        modifyRuleUsing: function ($rule, Forms\Get $get) {
//                            return $rule->where('company_id', $get('company_id'));
//                        }
//                    )
                        ->maxLength(255),
                    DatePicker::make('buy_at')
                        ->required()
                        ->default(today())
                        ->label('ყიდვის თარიღი')
                ]),

                Forms\Components\Grid::make(5)->schema([
                    PriceInput::make('price')
                        ->label('ფასი')
                        ->afterStateUpdated(fn(callable $set, callable $get) => self::calculateTotalPrice($set, $get)),
                    NumericInput::make('quantity')
                        ->label('რაოდენობა')
                        ->afterStateUpdated(fn(callable $set, callable $get) => self::calculateTotalPrice($set, $get)),
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
                        ->required()
                        ->searchable()
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
                Tables\Columns\TextColumn::make('company.company')
                    ->label('კომპანია')
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
                Tables\Columns\TextColumn::make('buy_at')
                    ->label('ყიდვის თარიღი')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('comment')
                    ->label('კომენტარი')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('დამატების თარიღი')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('company_id')
                    ->label(__('კომპანია'))
                    ->preload()
                    ->relationship('company', 'company'),
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
                    }),
                Tables\Filters\Filter::make('buy_at')
                    ->form([
                        DatePicker::make('buy_from')
                            ->label('თარიღიდან')
                            ->debounce(),
                        DatePicker::make('buy_to')
                            ->label('თარიღამდე')
                            ->debounce(),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['buy_from'], function (Builder $query, ?string $from) {
                                $query->where('buy_at', '>=', $from);
                            })
                            ->when($data['buy_to'], function (Builder $query, ?string $to) {
                                $query->where('buy_at', '<=', $to);
                            });
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('export_details')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->label('ექსელის ექსპორტი')
                    ->action(function ($record) {
                        $fileName = 'ნივთი_' . $record->company->company . '.xlsx';
                        return Excel::download(
                            new CompanyItemExport([$record]), $fileName
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
                            $fileName = 'ნივთები_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
                            return Excel::download(
                                new CompanyItemExport($records), $fileName
                            );
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CompanyRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanyItem::route('/'),
            'create' => Pages\CreateCompanyItem::route('/create'),
            'edit' => Pages\EditCompanyItem::route('/{record}/edit'),
        ];
    }

    private static function calculateTotalPrice(Forms\Set $set, Forms\Get $get): void
    {
        $set('total_price', getTotalPrice($get('price'), $get('quantity')));
    }
}
