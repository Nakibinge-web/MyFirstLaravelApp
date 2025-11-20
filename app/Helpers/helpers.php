<?php

use App\Helpers\CurrencyHelper;

if (!function_exists('currency_symbol')) {
    /**
     * Get currency symbol for current user or specified currency
     */
    function currency_symbol(?string $currency = null): string
    {
        return CurrencyHelper::symbol($currency);
    }
}

if (!function_exists('currency_format')) {
    /**
     * Format amount with currency symbol
     */
    function currency_format(float $amount, ?string $currency = null, int $decimals = 2): string
    {
        return CurrencyHelper::format($amount, $currency, $decimals);
    }
}

if (!function_exists('user_currency')) {
    /**
     * Get current user's currency
     */
    function user_currency(): string
    {
        return CurrencyHelper::current();
    }
}
