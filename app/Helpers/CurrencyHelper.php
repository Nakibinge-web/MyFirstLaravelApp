<?php

namespace App\Helpers;

class CurrencyHelper
{
    /**
     * Currency symbols mapping
     */
    private static $symbols = [
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'JPY' => '¥',
        'CNY' => '¥',
        'INR' => '₹',
        'CAD' => 'C$',
        'AUD' => 'A$',
        'CHF' => 'CHF',
        'SEK' => 'kr',
        'NZD' => 'NZ$',
        'KRW' => '₩',
        'SGD' => 'S$',
        'HKD' => 'HK$',
        'NOK' => 'kr',
        'MXN' => 'MX$',
        'BRL' => 'R$',
        'ZAR' => 'R',
        'RUB' => '₽',
        'TRY' => '₺',
        'AED' => 'د.إ',
        'SAR' => 'ر.س',
        'THB' => '฿',
        'IDR' => 'Rp',
        'MYR' => 'RM',
        'PHP' => '₱',
        'PLN' => 'zł',
        'DKK' => 'kr',
        'CZK' => 'Kč',
        'HUF' => 'Ft',
        'UGX' => 'UGX',
    ];

    /**
     * Get currency symbol for a given currency code
     */
    public static function symbol(?string $currency = null): string
    {
        $currency = $currency ?? auth()->user()->currency ?? 'USD';
        return self::$symbols[$currency] ?? $currency;
    }

    /**
     * Format amount with currency symbol
     */
    public static function format(float $amount, ?string $currency = null, int $decimals = 2): string
    {
        $currency = $currency ?? auth()->user()->currency ?? 'USD';
        $symbol = self::symbol($currency);
        
        // Format number with commas
        $formatted = number_format($amount, $decimals);
        
        // Position symbol based on currency
        if (in_array($currency, ['EUR', 'SEK', 'NOK', 'DKK', 'CZK', 'HUF', 'PLN'])) {
            // Symbol after amount for these currencies
            return $formatted . ' ' . $symbol;
        }
        
        // Symbol before amount for most currencies
        return $symbol . $formatted;
    }

    /**
     * Get user's current currency
     */
    public static function current(): string
    {
        return auth()->user()->currency ?? 'USD';
    }
}
