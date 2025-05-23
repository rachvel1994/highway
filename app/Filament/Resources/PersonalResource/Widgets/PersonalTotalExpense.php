<?php

namespace App\Filament\Resources\PersonalResource\Widgets;

use App\Filament\Resources\PersonalResource\Pages\ListPersonal;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PersonalTotalExpense extends StatsOverviewWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListPersonal::class;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('ხარჯი', money(
                $this->getPageTableQuery()
                    ->join('work_asset_details', 'personals.id', '=', 'work_asset_details.personal_id')
                    ->sum('person_salary_total')
            ))
        ];
    }
}

