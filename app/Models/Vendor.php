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
        $day = strtolower(now()->format('l'));
        $today = $hours[$day] ?? [];

        if (! ($today['is_open'] ?? false)) {
            return false;
        }

        $now = now()->format('H:i');
        $open  = $today['open']  ?? '09:00';
        $close = $today['close'] ?? '22:00';

        return $now >= $open && $now < $close;
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
