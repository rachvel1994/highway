<?php

use Illuminate\Support\Number;

if (!function_exists('money')) {
    function money(mixed $money = 0, string $currency = 'GEL'): string
    {
        return Number::currency($money ?? 0, $currency, app()->getLocale());
    }
}
