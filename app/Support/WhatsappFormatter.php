<?php

namespace App\Support;

use App\Models\Order;

/**
 * Builds pre-formatted WhatsApp message bodies for Wassili.
 * Returns a fully built https://wa.me/ URL ready to open in a new tab.
 *
 * Messages are written in Arabic. Item, store and customer names are printed
 * exactly as they were saved, so an English product stays English inside an
 * otherwise Arabic message.
 *
 * When "show prices" is switched off in Admin → Settings, no money appears
 * anywhere in the message — the call centre prices the order by phone instead.
 */
class WhatsappFormatter
{
    /** Dispatch message sent to a DRIVER when an order is assigned. */
    public static function driverDispatchUrl(Order $order): string
    {
        $phone      = self::normalizePhone($order->driver?->phone ?? '');
        $showPrices = Settings::showPriceOnMainPage();

        $lines   = [];
        $lines[] = '🛵 *طلب توصيل جديد — وصّلي*';
        $lines[] = '——————————————';
        $lines[] = '👤 الزبون: '.$order->customer_name;
        $lines[] = '📞 الهاتف: '.self::formatPhone($order->customer_phone);
        $lines[] = '📍 العنوان: '.$order->address;

        // Tappable pin: opens the driver's Maps app straight into navigation.
        if ($order->hasLocation()) {
            $lines[] = '🗺️ الموقع على الخريطة: '.$order->maps_url;
        }

        if (! empty($order->notes)) {
            $lines[] = '📝 ملاحظات: '.$order->notes;
        }

        $lines[] = '🔖 رقم الطلب: '.$order->tracking_number;
        $lines[] = '——————————————';
        $lines[] = '🛒 *الطلبية:*';

        foreach ((array) $order->items as $item) {
            $lines[] = self::formatItemLine($item, $showPrices);
        }

        if ($showPrices) {
            $lines[] = '——————————————';
            $lines[] = '💵 المجموع: '.Money::both((float) $order->total_price - (float) $order->delivery_fee);
            $lines[] = '🚚 التوصيل: '.Money::both((float) $order->delivery_fee);
            $lines[] = '✅ *الإجمالي: '.Money::both((float) $order->total_price).'*';
        }

        return 'https://wa.me/'.$phone.'?text='.rawurlencode(implode("\n", $lines));
    }

    protected static function formatItemLine(array $item, bool $showPrices): string
    {
        $qty  = $item['quantity'] ?? 1;
        $name = $item['name'] ?? '—';
        $line = "• {$qty}× {$name}";

        // Universal-catalog / custom items carry a store hint or a note.
        if (! empty($item['vendor'])) {
            $line .= " ({$item['vendor']})";
        } elseif (! empty($item['is_custom'])) {
            $line .= ' (طلب خاص)';
        }

        if (! empty($item['note'])) {
            $line .= " — {$item['note']}";
        }

        if ($showPrices && isset($item['price']) && (float) $item['price'] > 0) {
            $line .= ' — '.Money::both((float) $item['price'] * $qty);
        }

        return $line;
    }

    /** Display a local number with its dialling code, e.g. +961 71123456. */
    public static function formatPhone(?string $phone): string
    {
        return Settings::formatPhone($phone);
    }

    /** Strip everything except digits so wa.me receives a clean number. */
    public static function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }
}
