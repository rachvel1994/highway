<?php

namespace App\Filament\Resources\DamageResource\Widgets;

use App\Filament\Resources\DamageResource\Pages\ListDamages;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DamageTotalExpense extends StatsOverviewWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListDamages::class;
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

