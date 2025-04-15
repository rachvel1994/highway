<?php

namespace App\Filament\Widgets;

use App\Models\CompanyItem;
use App\Models\Damage;
use App\Models\Equipment;
use App\Models\Factory;
use App\Models\Fuel;
use App\Models\Personal;
use App\Models\Product;
use App\Models\WorkAsset;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{

    protected static ?string $pollingInterval = '10s';


    protected function getHeading(): string
    {
        return 'სრული სტატისტიკა';
    }

    protected function getStats(): array
    {
        return [
            Stat::make('ობიექტის ხარჯი', money(WorkAsset::query()->sum('grand_total'))),
            Stat::make('ქარხანის ხარჯი', money(Factory::query()->sum('total_price'))),
            Stat::make('კომპანიის ხარჯი', money(CompanyItem::query()->sum('total_price'))),
            Stat::make('დაზიანების ხარჯი', money(Damage::query()->sum('total_price'))),
            Stat::make('ნაქირავები ტექნიკის ხარჯი', money(Equipment::query()->where('type', 'rent')->sum('price'))),
            Stat::make('საწვავის ხარჯი', money(Fuel::query()->sum('total_price'))),
            Stat::make('საწვავის რაოდენობა', Fuel::query()->sum('quantity') . ' ლიტრი'),
            Stat::make('საწვავის ნაშთი', Fuel::query()->sum('remain') . ' ლიტრი'),
            Stat::make('პერსონალის ხარჯი', money(Personal::query()->join('work_asset_details', 'personals.id', '=', 'work_asset_details.personal_id')
                ->sum('person_salary_total'))),
            Stat::make('მაღაზიის ხარჯი', money(Product::query()->sum('total_price'))),
        ];
    }
}
