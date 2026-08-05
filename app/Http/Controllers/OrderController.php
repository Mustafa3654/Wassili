<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Persist a cart posted from the Alpine checkout as a 'pending' order.
     * Prices and the delivery fee are recomputed server-side so a tampered
     * client payload can never change what is charged.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_name'  => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:40'],
            'address'        => ['required', 'string', 'max:1000'],
            'notes'          => ['nullable', 'string', 'max:1000'],
            // Optional GPS pin shared from the customer's browser.
            'latitude'          => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'         => ['nullable', 'numeric', 'between:-180,180'],
            'location_accuracy' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'items'          => ['required', 'array', 'min:1'],
            'items.*.name'       => ['required', 'string', 'max:255'],
            'items.*.quantity'   => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.vendor_id'  => ['nullable', 'integer'],
            'items.*.is_custom'  => ['nullable', 'boolean'],
            'items.*.note'       => ['nullable', 'string', 'max:500'],
        ]);

        // Rebuild each line with an authoritative price from the DB.
        $lines = [];
        $subtotal = 0.0;

        foreach ($data['items'] as $item) {
            $price = 0.0;
            $vendorId = $item['vendor_id'] ?? null;
            $vendorName = null;

            if (! empty($item['product_id'])) {
                $product = Product::with('vendor')->find($item['product_id']);
                if (! $product || ! $product->is_available) {
                    continue; // silently drop unavailable items
                }
                $price      = (float) $product->price;
                $vendorId   = $product->vendor_id;
                $vendorName = $product->vendor?->name;
            }

            $qty = (int) $item['quantity'];
            $subtotal += $price * $qty;

            $lines[] = [
                'product_id' => $item['product_id'] ?? null,
                'name'       => $item['name'],
                'quantity'   => $qty,
                'price'      => $price,
                'vendor_id'  => $vendorId,
                'vendor'     => $vendorName,
                'is_custom'  => (bool) ($item['is_custom'] ?? false),
                'note'       => $item['note'] ?? null,
            ];
        }

        abort_if(empty($lines), 422, 'No orderable items in cart.');

        $deliveryFee = $this->calculateDeliveryFee($lines);

        $order = Order::create([
            'customer_name'  => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'address'        => $data['address'],
            'notes'          => $data['notes'] ?? null,
            // Only store a pin when both halves arrived.
            'latitude'          => isset($data['longitude']) ? ($data['latitude'] ?? null) : null,
            'longitude'         => isset($data['latitude']) ? ($data['longitude'] ?? null) : null,
            'location_accuracy' => $data['location_accuracy'] ?? null,
            'items'          => $lines,
            'total_price'    => $subtotal + $deliveryFee,
            'delivery_fee'   => $deliveryFee,
            'status'         => 'pending',
        ]);

        return response()->json([
            'ok'              => true,
            'tracking_number' => $order->tracking_number,
        ], 201);
    }

    /**
     * Base fee + an incremental fee for each additional distinct pickup point.
     * Universal / custom (vendor-less) items collapse into one "loose" group.
     */
    protected function calculateDeliveryFee(array $lines): float
    {
        $vendorIds = collect($lines)->pluck('vendor_id')->filter()->unique();
        $hasLoose  = collect($lines)->contains(fn ($l) => empty($l['vendor_id']));

        $distinctPickups = $vendorIds->count() + ($hasLoose ? 1 : 0);

        $base  = \App\Support\Settings::baseDeliveryFee();
        $extra = \App\Support\Settings::multiVendorFee();

        return $base + max(0, $distinctPickups - 1) * $extra;
    }
}
