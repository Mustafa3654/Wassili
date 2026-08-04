<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    protected $fillable = [
        'name', 'name_ar', 'slug', 'category_id', 'phone', 'address',
        'logo', 'is_active', 'opening_hours',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'opening_hours' => 'array',
    ];

    public function getIsOpenAttribute(): bool
    {
        $hours = $this->opening_hours ?? [];

        // No schedule configured yet => treat the store as always open.
        // Failing closed would hide every vendor until an admin fills in
        // seven days of hours, which makes the storefront unusable.
        if (empty($hours)) {
            return true;
        }

        $today = $hours[strtolower(now()->format('l'))] ?? [];

        if (! ($today['is_open'] ?? false)) {
            return false;
        }

        $now = now()->format('H:i');

        return $now >= ($today['open'] ?? '09:00')
            && $now <  ($today['close'] ?? '22:00');
    }

    /**
     * "09:00"-style opening time for today, or null when the store either has
     * no schedule or is closed all day. Used to say *when* a store reopens
     * instead of a dead-end "Closed" badge.
     */
    public function getOpensAtAttribute(): ?string
    {
        $today = ($this->opening_hours ?? [])[strtolower(now()->format('l'))] ?? [];

        return ($today['is_open'] ?? false) ? ($today['open'] ?? null) : null;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getLabelAttribute(): string
    {
        return app()->getLocale() === 'ar' && $this->name_ar
            ? $this->name_ar
            : $this->name;
    }
}
