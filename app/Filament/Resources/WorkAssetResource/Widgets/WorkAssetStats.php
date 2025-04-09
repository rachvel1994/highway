<?php

namespace App\Filament\Resources\WorkAssetResource\Widgets;

use App\Filament\Resources\WorkAssetResource\Pages\ListWorkAssets;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WorkAssetStats extends StatsOverviewWidget
{
    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListWorkAssets::class;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('ხარჯი', money($this->getPageTableQuery()
                ->sum('grand_total') ?? 0))
        ];
    }
}

