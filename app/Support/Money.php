<?php

namespace App\Support;

/**
 * Dual-currency formatter for Reva (Lebanon).
 *
 * Prices are stored in USD (the base currency) and displayed alongside their
 * LBP equivalent using the configurable rate in config('reva.currency').
 *
 *   Money::both(5.0)  =>  "$5.00 · 445,000 LL"   (en)
 *                          "$5.00 · 445,000 ل.ل" (ar)
 */
class Money
{
    public static function usd(float $amount): string
    {
        return config('reva.currency.usd_symbol', '$').number_format($amount, 2);
    }

    public static function lbp(float $amount): string
    {
        return number_format($amount * Settings::lbpRate()).' '.self::lbpLabel();
    }

    /** Both currencies in one string — the default display everywhere. */
    public static function both(float $amount): string
    {
        return self::usd($amount).' · '.self::lbp($amount);
    }

    public static function lbpLabel(): string
    {
        return app()->getLocale() === 'ar' ? 'ل.ل' : 'LL';
    }
}
