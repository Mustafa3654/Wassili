<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    protected $fillable = [
        'name', 'phone', 'vehicle_type', 'working_hours', 'delivery_fee',
        'status', 'is_active',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'working_hours' => 'array',
        'delivery_fee'  => 'decimal:2',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Whether the driver is inside their shift right now.
     *
     * No shift configured means always on duty — otherwise every driver would
     * vanish from dispatch until an admin filled in seven days of hours.
     */
    public function getIsOnShiftAttribute(): bool
    {
        $hours = $this->working_hours ?? [];

        if (empty($hours)) {
            return true;
        }

        $today = $hours[strtolower(now()->format('l'))] ?? [];

        if (! ($today['is_open'] ?? false)) {
            return false;
        }

        $now = now()->format('H:i');

        return $now >= ($today['open'] ?? '00:00')
            && $now <  ($today['close'] ?? '23:59');
    }

    /** "09:00"-style start time for today, or null when off all day. */
    public function getShiftStartsAtAttribute(): ?string
    {
        $today = ($this->working_hours ?? [])[strtolower(now()->format('l'))] ?? [];

        return ($today['is_open'] ?? false) ? ($today['open'] ?? null) : null;
    }

    /**
     * What the dispatcher actually needs to know, combining the manual status
     * with the shift. A driver marked "available" who is off-shift is not
     * available, so this is what the badge and the dispatch list both use.
     */
    public function getAvailabilityAttribute(): string
    {
        if (! $this->is_active) {
            return 'inactive';
        }

        if ($this->status === 'busy') {
            return 'busy';
        }

        if ($this->status === 'offline') {
            return 'offline';
        }

        return $this->is_on_shift ? 'available' : 'off_shift';
    }

    public function isDispatchable(): bool
    {
        return $this->availability === 'available';
    }

    /**
     * Drivers who could take an order now. Shifts live in JSON, so the shift
     * check happens in PHP after the cheap column filters.
     */
    public function scopeDispatchable(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('status', 'available');
    }

    /** Only the drivers actually on shift, as a collection. */
    public static function availableNow()
    {
        return static::dispatchable()->get()->filter->is_on_shift->values();
    }

    /** Per-driver delivery fee, or null to keep the order's existing fee. */
    public function overrideFee(): ?float
    {
        return $this->delivery_fee !== null ? (float) $this->delivery_fee : null;
    }
}
