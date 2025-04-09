<?php

namespace App\Filament\Resources\CompanyResource\Widgets;

use App\Filament\Resources\CompanyResource\Pages\ListCompanies;
use Filament\Tables\Filters\Concerns\InteractsWithTableQuery;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CompanyTotalExpense extends StatsOverviewWidget
{
    use InteractsWithPageTable, InteractsWithTableQuery;

    protected function getTablePage(): string
    {
        return ListCompanies::class;
    }

    /**
     * @return array|Stat[]
     */
    protected function getStats(): array
    {

        return [
            Stat::make('ხარჯი', money(
                $this->getPageTableQuery()
                    ->leftJoin('company_items', 'companies.id', '=', 'company_items.company_id')
                    ->sum('total_price') ?? 0
            ))
        ];
    }


}

