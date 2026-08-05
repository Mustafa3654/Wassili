<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'customer_name', 'customer_phone', 'address', 'notes',
        'latitude', 'longitude', 'location_accuracy',
        'tracking_number', 'items', 'total_price', 'delivery_fee',
        'status', 'driver_id',
    ];

    protected $casts = [
        'items'        => 'array',
        'total_price'  => 'decimal:2',
        'delivery_fee' => 'decimal:2',
    ];

    /** True when the customer shared a GPS pin at checkout. */
    public function hasLocation(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /**
     * Google Maps link for the pin. Opens the native Maps app on a phone and
     * the website on desktop, so the driver can start navigation in one tap.
     */
    public function getMapsUrlAttribute(): ?string
    {
        return $this->hasLocation()
            ? 'https://maps.google.com/?q='.$this->latitude.','.$this->longitude
            : null;
    }

    protected static function booted(): void
    {
        // Guarantee every order has a unique public tracking number.
        static::creating(function (Order $order) {
            if (empty($order->tracking_number)) {
                $order->tracking_number = static::generateTrackingNumber();
            }
        });
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public static function generateTrackingNumber(): string
    {
        do {
            // e.g. WSL-8F3A2C7B — short, human-readable, unguessable.
            $candidate = 'WSL-'.strtoupper(Str::random(8));
        } while (static::where('tracking_number', $candidate)->exists());

        return $candidate;
    }

    /**
     * The four ordered pipeline steps used by the public tracking page.
     * 'cancelled' is handled separately as a terminal error state.
     */
    public const PIPELINE = ['pending', 'in_progress', 'delivered'];

    /** Zero-based index of the current status inside the pipeline (or -1). */
    public function progressIndex(): int
    {
        return array_search($this->status, self::PIPELINE, true) ?: 0;
    }
}
