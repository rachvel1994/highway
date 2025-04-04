<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyItemResource\Pages;
use App\Models\CompanyItem;
use Exception;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                Forms\Components\Select::make('company_id')
                    ->label('კომპანია')
                    ->preload()
                    ->unique(ignoreRecord: true)
                    ->relationship('company', 'title')
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->label('სახელი')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Grid::make(4)->schema([
                    Forms\Components\TextInput::make('price')
                        ->label('ფასი')
                        ->required()
                        ->numeric()
                        ->default(0)
                        ->postfix('₾'),
                    Forms\Components\TextInput::make('quantity')
                        ->label('რაოდენობა')
                        ->required()
                        ->numeric()
                        ->default(0),
                    Forms\Components\Select::make('category_id')
                        ->label('კატეგორია')
                        ->searchable()
                        ->preload()
                        ->relationship('category', 'title'),
                    Forms\Components\Select::make('measure_id')
                        ->label('საზომი ერთეული')
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
                Tables\Columns\TextColumn::make('company.title')
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
                Tables\Columns\TextColumn::make('comment')
                    ->label('კომენტარი')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('დამატების თარიღი')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('company_id')
                    ->label(__('კომპანია'))
                    ->preload()
                    ->relationship('company', 'title'),
                Tables\Filters\SelectFilter::make('category_id')
                    ->label(__('კატეგორია'))
                    ->preload()
                    ->relationship('category', 'title'),
                Tables\Filters\SelectFilter::make('measure_id')
                    ->label(__('საზომი ერთეული'))
                    ->preload()
                    ->relationship('measure', 'short_title'),
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
            'index' => Pages\ListCompanyItem::route('/'),
            'create' => Pages\CreateCompanyItem::route('/create'),
            'edit' => Pages\EditCompanyItem::route('/{record}/edit'),
        ];
    }
}
