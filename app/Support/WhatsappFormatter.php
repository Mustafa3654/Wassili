<?php

namespace App\Support;

use App\Models\Order;

/**
 * Builds pre-formatted, human-readable WhatsApp message bodies for Reva.
 * Returns a fully built https://wa.me/ URL ready to open in a new tab.
 */
class WhatsappFormatter
{
    /**
     * Dispatch message sent to a DRIVER when an order is assigned.
     */
    public static function driverDispatchUrl(Order $order): string
    {
        $phone = self::normalizePhone($order->driver?->phone ?? '');

        $lines = [];
        $lines[] = '🛵 *مهمة توصيل جديدة — Reva*';
        $lines[] = '——————————————';
        $lines[] = '👤 العميل / Customer: '.$order->customer_name;
        $lines[] = '📞 الهاتف / Phone: '.$order->customer_phone;
        $lines[] = '📍 العنوان / Address: '.$order->address;

        if (! empty($order->notes)) {
            $lines[] = '📝 ملاحظات / Notes: '.$order->notes;
        }

        $lines[] = '🔖 رقم التتبع / Tracking: '.$order->tracking_number;
        $lines[] = '——————————————';
        $lines[] = '🛒 *الطلبات / Items:*';

        foreach ((array) $order->items as $item) {
            $lines[] = self::formatItemLine($item);
        }

        $lines[] = '——————————————';
        $lines[] = '💵 قيمة الطلب / Subtotal: '.Money::both((float) $order->total_price - (float) $order->delivery_fee);
        $lines[] = '🚚 رسوم التوصيل / Delivery: '.Money::both((float) $order->delivery_fee);
        $lines[] = '✅ *الإجمالي / Total: '.Money::both((float) $order->total_price).'*';

        $text = implode("\n", $lines);

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($text);
    }

    protected static function formatItemLine(array $item): string
    {
        $qty   = $item['quantity'] ?? 1;
        $name  = $item['name'] ?? '—';
        $line  = "• {$qty}× {$name}";

        // Universal-catalog / custom items carry a vendor hint or note.
        if (! empty($item['vendor'])) {
            $line .= " ({$item['vendor']})";
        } elseif (! empty($item['is_custom'])) {
            $line .= ' (طلب خاص / Custom)';
        }

        if (! empty($item['note'])) {
            $line .= " — {$item['note']}";
        }

        if (isset($item['price']) && (float) $item['price'] > 0) {
            $line .= ' — '.Money::both((float) $item['price'] * $qty);
        }

        return $line;
    }

    /** Strip everything except digits so wa.me receives a clean number. */
    public static function normalizePhone(string $phone): string
    {
        return preg_replace('/\D+/', '', $phone) ?? '';
    }
}
