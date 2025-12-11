<?php

if (!function_exists('format_currency')) {
    /**
     * Format currency based on application settings
     *
     * @param float|int|null $amount
     * @param string|null $currency
     * @param int $decimals
     * @return string
     */
    function format_currency($amount, ?string $currency = null, int $decimals = 0): string
    {
        if ($amount === null) {
            return '-';
        }

        // Get currency from settings or use default
        $currency = $currency ?? config('app.currency', 'IDR');
        
        // Currency symbols mapping
        $symbols = [
            'IDR' => 'Rp',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'SGD' => 'S$',
            'MYR' => 'RM',
        ];

        $symbol = $symbols[$currency] ?? $currency;
        
        // Format based on currency
        if ($currency === 'IDR') {
            // Indonesian format: Rp 1.000.000
            return $symbol . ' ' . number_format($amount, $decimals, ',', '.');
        } else {
            // International format: $1,000,000.00
            return $symbol . number_format($amount, $decimals, '.', ',');
        }
    }
}

if (!function_exists('get_currency_symbol')) {
    /**
     * Get currency symbol
     *
     * @param string|null $currency
     * @return string
     */
    function get_currency_symbol(?string $currency = null): string
    {
        $currency = $currency ?? config('app.currency', 'IDR');
        
        $symbols = [
            'IDR' => 'Rp',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'SGD' => 'S$',
            'MYR' => 'RM',
        ];

        return $symbols[$currency] ?? $currency;
    }
}
