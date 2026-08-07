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
        return (float) self::value('lbp_rate', config('wassili.currency.lbp_rate', 89000));
    }

    public static function baseDeliveryFee(): float
    {
        return (float) self::value('base_delivery_fee', config('wassili.base_delivery_fee', 2));
    }

    public static function multiVendorFee(): float
    {
        return (float) self::value('multi_vendor_fee', config('wassili.multi_vendor_fee', 1));
    }

    public static function callCenterNumber(): string
    {
        return (string) self::value('call_center_number', config('wassili.call_center_number', ''));
    }

    public static function showPriceOnMainPage(): bool
    {
        return (bool) self::value('show_price_on_main_page', config('wassili.show_price_on_main_page', true));
    }

    /**
     * Dialling code shown in front of every local phone number, e.g. "+961".
     * Editable so the app isn't pinned to one country.
     */
    public static function countryCode(): string
    {
        return (string) self::value('country_code', config('wassili.country_code', '+961'));
    }

    /** "+961 71123456", or an em dash when there's no number. */
    public static function formatPhone(?string $phone): string
    {
        $phone = trim((string) $phone);

        return $phone === '' ? '—' : self::countryCode().' '.$phone;
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
