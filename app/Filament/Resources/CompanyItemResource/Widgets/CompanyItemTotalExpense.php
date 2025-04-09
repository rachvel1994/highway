<?php

namespace App\Filament\Resources\CompanyItemResource\Widgets;

use App\Filament\Resources\CompanyItemResource\Pages\ListCompanyItem;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CompanyItemTotalExpense extends StatsOverviewWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListCompanyItem::class;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('ხარჯი', money(
                $this->getPageTableQuery()->sum('total_price')
            ))
        ];
    }

}

