<?php

use App\Models\Company;
use App\Models\CompanyItem;
use App\Models\Equipment;
use App\Models\Fuel;
use App\Models\Personal;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Number;

if (!function_exists('money')) {
    function money(mixed $money = 0, string $currency = 'GEL'): string
    {
        return Number::currency(str_replace(',', '.', $money) ?? 0, $currency, app()->getLocale());
    }
}

if (!function_exists('recalculateSalary')) {
    function recalculateSalary(callable $set, callable $get): void
    {
        $salary = (float)$get('person_salary') ?? 0;
        $type = $get('person_salary_type');

        if ($type == 1) {
            $set('person_salary_total', $salary);
        } elseif ($type == 2) {
            $days = (int)$get('person_worked_days') ?: 1;
            $set('person_salary_total', $salary * $days);
        } elseif ($type == 3 || $type == 4) {
            $qty = (int)$get('person_worked_quantity') ?: 1;
            $set('person_salary_total', $salary * $qty);
        }
    }
}


if (!function_exists('getItemsByCompanyId')) {
    function getItemsByCompanyId(int $id): array
    {
        return CompanyItem::with('company')
            ->where('company_id', $id)
            ->get()
            ->mapWithKeys(fn($item) => [$item->id => $item->title_with_company])
            ->toArray() ?? [];
    }
}

if (!function_exists('getProductsByStoreId')) {
    function getProductsByStoreId(int $id): array
    {
        return Product::with('store')
            ->where('store_id', $id)
            ->get()
            ->mapWithKeys(fn($product) => [$product->id => $product->title_with_store])
            ->toArray() ?? [];
    }
}

if (!function_exists('getProductById')) {
    function getProductById(?int $id = null): ?Product
    {
        return Product::query()->where('id', $id)->first();
    }
}

if (!function_exists('getEquipmentById')) {
    function getEquipmentById(?int $id = null): ?Equipment
    {
        return Equipment::query()->with('damages')->where('id', $id)->first();
    }
}

if (!function_exists('getPersonById')) {
    function getPersonById(?int $id = null): ?Personal
    {
        return Personal::query()->where('id', $id)->first();
    }
}

if (!function_exists('getItemById')) {
    function getItemById(?int $itemId = null): ?CompanyItem
    {
        return CompanyItem::query()->where('id', $itemId)->first();
    }
}

if (!function_exists('getCompanyById')) {
    function getCompanyById(?int $id = null): ?Company
    {
        return Company::query()->where('id', $id)->first();
    }
}


if (!function_exists('getStoreById')) {
    function getStoreById(?int $id = null): ?Store
    {
        return Store::query()->where('id', $id)->first();
    }
}


if (!function_exists('getFuelById')) {
    function getFuelById(?int $id = null): ?Fuel
    {
        return Fuel::query()->where('id', $id)->first();
    }
}

if (!function_exists('getTotalPrice')) {
    function getTotalPrice(mixed $price = 0, mixed $quantity = 0): float
    {
        return (float)$price * (float)$quantity;
    }
}
