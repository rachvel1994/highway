<?php

namespace App\Filament\Resources\FactoryResource\Widgets;

use App\Filament\Resources\FactoryResource\Pages\ListFactories;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FactoryTotalExpense extends StatsOverviewWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListFactories::class;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('ხარჯი', money(
                $this->getPageTableQuery()
                    ->sum('total_price') ?? 0
            )),
        ];
    }
}

