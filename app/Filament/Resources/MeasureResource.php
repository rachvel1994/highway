<?php

namespace App\Filament\Resources;

use App\Exports\MeasureExport;
use App\Filament\Resources\MeasureResource\Pages;
use App\Models\Measure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class MeasureResource extends Resource
{
    protected static ?string $model = Measure::class;
    protected static ?string $navigationLabel = 'საზომი ერთეული';

    protected static ?string $breadcrumb = 'საზომი ერთეული';

    protected static ?string $modelLabel = 'საზომი ერთეული';
    protected static ?string $navigationIcon = 'heroicon-o-scale';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('სათაური')
                    ->unique(ignoreRecord: true)
                    ->required(),
                Forms\Components\TextInput::make('short_title')
                    ->label('მოკ. სათაური')
                    ->required(),
                Forms\Components\Textarea::make('comment')
                    ->rows(5)
                    ->columnSpanFull()
                    ->label('კომენტარი'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('სათაური')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('short_title')
                    ->label('მოკ. სათაური')
                    ->searchable()
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
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('export_details')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->label('ექსელის ექსპორტი')
                    ->action(function ($record) {
                        $fileName = 'საზომი_ერთეული_' . $record->title . '.xlsx';
                        return Excel::download(
                            new MeasureExport([$record]), $fileName
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
                            $fileName = 'საზომი_ერთეულები_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
                            return Excel::download(
                                new MeasureExport($records), $fileName
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
            'index' => Pages\ListMeasures::route('/'),
            'create' => Pages\CreateMeasure::route('/create'),
            'edit' => Pages\EditMeasure::route('/{record}/edit'),
        ];
    }
}
