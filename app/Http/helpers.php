<?php

use Illuminate\Support\Number;

if (!function_exists('money')) {
    function money(mixed $money = 0, string $currency = 'GEL'): string
    {
        return Number::currency($money ?? 0, $currency, app()->getLocale());
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
        } elseif ($type == 3) {
            $qty = (int)$get('person_worked_quantity') ?: 1;
            $set('person_salary_total', $salary * $qty);
        }
    }
}
