<?php

namespace App\Filament\Resources\FuelResource\Widgets;

use App\Filament\Resources\FuelResource\Pages\ListFuels;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FuelStats extends StatsOverviewWidget
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
                ->sum('total_price') ?? 0)),
            Stat::make('რაოდენობა', $this->getPageTableQuery()
                ->sum('quantity')),
            Stat::make('ნაშთი', $this->getPageTableQuery()
                ->sum('remain'))
        ];
    }
}

