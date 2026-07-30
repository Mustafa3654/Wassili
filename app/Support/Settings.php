<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Typed accessors for admin-editable business settings.
 *
 * Each value comes from the `settings` table when present, otherwise it falls
 * back to the config()/.env default. Wrapped in try/catch so the app still
 * boots before the settings table exists (fresh install, pre-migration).
 */
class Settings
{
    public static function lbpRate(): float
    {
        return (float) self::value('lbp_rate', config('reva.currency.lbp_rate', 89000));
    }

    public static function baseDeliveryFee(): float
    {
        return (float) self::value('base_delivery_fee', config('reva.base_delivery_fee', 2));
    }

    public static function multiVendorFee(): float
    {
        return (float) self::value('multi_vendor_fee', config('reva.multi_vendor_fee', 1));
    }

    public static function callCenterNumber(): string
    {
        return (string) self::value('call_center_number', config('reva.call_center_number', ''));
    }

    public static function showPriceOnMainPage(): bool
    {
        return (bool) self::value('show_price_on_main_page', config('reva.show_price_on_main_page', true));
    }

    /** Returns the stored value, or $default when missing/empty/unavailable. */
    protected static function value(string $key, $default)
    {
        try {
            $stored = Setting::get($key, null);

            return ($stored === null || $stored === '') ? $default : $stored;
        } catch (\Throwable $e) {
            return $default;
        }
    }
}
