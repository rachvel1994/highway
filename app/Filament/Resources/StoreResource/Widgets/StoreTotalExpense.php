<?php

namespace App\Filament\Resources\StoreResource\Widgets;

use App\Filament\Resources\StoreResource\Pages\ListStores;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StoreTotalExpense extends StatsOverviewWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListStores::class;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('ხარჯი', money($this->getPageTableQuery()
                ->selectRaw('SUM(price * quantity) as total_expense')
                ->value('total_expense')))
        ];
    }
}

