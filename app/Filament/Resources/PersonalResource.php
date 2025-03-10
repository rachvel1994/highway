<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonalResource\Pages;
use App\Models\Personal;
use Exception;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PersonalResource extends Resource
{
    protected static ?string $model = Personal::class;

    protected static ?string $navigationLabel = 'პერსონალი';

    protected static ?string $breadcrumb = 'პერსონალი';

    protected static ?string $modelLabel = 'პერსონალი';
    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('full_name')
                    ->label('სახელი, გვარი')
                    ->required(),
                Forms\Components\TextInput::make('salary')
                    ->label('ხელფასი')
                    ->postfix('₾')
                    ->default(0)
                    ->numeric(),
                Forms\Components\Select::make('salary_type')
                    ->label('ხელფასის ტიპი')
                    ->options([
                        1 => 'თვიური',
                        2 => 'დღიური',
                    ])
                    ->default(1)
                    ->reactive(),
                Forms\Components\TextInput::make('worked_days')
                    ->label('ნამუშევარი დღეები')
                    ->numeric()
                    ->default(0)
                    ->visible(fn(callable $get) => $get('salary_type') == 2),
                Forms\Components\Textarea::make('comment')
                    ->rows(5)
                    ->columnSpanFull()
                    ->label('კომენტარი'),
            ]);
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('სახელი, გვარი')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('salary')
                    ->label('ხელფასი')
                    ->searchable()
                    ->money('GEL')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('salary_type')
                    ->label('ხელფასის ტიპი')
                    ->formatStateUsing(fn($state) => match ($state) {
                        1 => 'თვიური',
                        2 => 'დღიური',
                        default => $state,
                    })
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('worked_days')
                    ->label('ნამუშევარი დღეები')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
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
                Tables\Filters\SelectFilter::make('salary_type')
                    ->label('ხელფასის ტიპი')
                    ->options([
                        1 => 'თვიური',
                        2 => 'დღიური',
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
            'index' => Pages\ListPersonal::route('/'),
            'create' => Pages\CreatePersonal::route('/create'),
            'edit' => Pages\EditPersonal::route('/{record}/edit'),
        ];
    }
}
