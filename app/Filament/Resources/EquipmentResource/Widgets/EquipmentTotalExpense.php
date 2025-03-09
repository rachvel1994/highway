<?php

namespace App\Filament\Resources\EquipmentResource\Widgets;

use App\Filament\Resources\EquipmentResource\Pages\ListEquipment;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EquipmentTotalExpense extends StatsOverviewWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListEquipment::class;
    }

    protected function getStats(): array
    {
        return [
//            Stat::make('ხარჯი', money($this->getPageTableQuery()
//                ->selectRaw('SUM((detail_price * quantity) + craft_price + additional_expense) as total_expense')
//                ->value('total_expense'))) ?? 0
        ];
    }
}

