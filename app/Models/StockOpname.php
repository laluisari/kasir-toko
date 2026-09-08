<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockOpname extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockOpnameItem::class);
    }

    public function summary(): array
    {
        $items = $this->items->groupBy('status');

        return [
            'total' => $this->items->count(),
            'dihitung' => $items->get('pas', collect())->count() + $items->get('selisih', collect())->count(),
            'pas' => $items->get('pas', collect())->count(),
            'selisih' => $items->get('selisih', collect())->count(),
            'total_selisih' => $this->items->sum('selisih') ?? 0,
        ];
    }
}