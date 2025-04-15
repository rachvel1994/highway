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
        $damageTotal = $this->getPageTableQuery()
            ->join('damages', 'equipment.id', '=', 'damages.equipment_id')
            ->sum('total_price') ?? 0;

        $rentTotal = $this->getPageTableQuery()->sum('price') ?? 0;

        return [
            Stat::make('სრული ხარჯი', money($damageTotal + $rentTotal ?? 0)),
            Stat::make('დაზიანების ხარჯი', money($damageTotal)),
            Stat::make('ნაქირავები ტექნიკის ხარჯი', money($rentTotal)),
        ];
    }
}

