<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $fillable = [
        'name', 'name_ar', 'description', 'description_ar', 'price',
        'image', 'category_id', 'vendor_id', 'is_available',
    ];

    protected $casts = [
        'price'        => 'decimal:2',
        'is_available' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    /** True when this item belongs to the shared Universal Catalog. */
    public function isUniversal(): bool
    {
        return is_null($this->vendor_id);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    public function getLabelAttribute(): string
    {
        return app()->getLocale() === 'ar' && $this->name_ar
            ? $this->name_ar
            : $this->name;
    }
}
