<?php

namespace App\Filament\Resources\FuelResource\Widgets;

use App\Filament\Resources\FuelResource\Pages\ListFuels;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FuelTotalExpense extends StatsOverviewWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListFuels::class;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('ხარჯი', money($this->getPageTableQuery()
                ->selectRaw('SUM((price * quantity) - (price * remain)) as total_expense')
                ->value('total_expense')))
        ];
    }
}

