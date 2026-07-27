<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    protected $fillable = [
        'name', 'phone', 'vehicle_type', 'status', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** Drivers eligible to receive a dispatch: active AND currently available. */
    public function scopeDispatchable(Builder $query): Builder
    {
        return $query->where('is_active', true)
                     ->where('status', 'available');
    }
}
