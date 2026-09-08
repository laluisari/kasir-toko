<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpnameItem extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'stok_fisik' => 'integer',
            'stok_sistem' => 'integer',
            'selisih' => 'integer',
            'terjual' => 'integer',
            'stok_penetapan' => 'integer',
            'counted_at' => 'datetime',
        ];
    }

    public function stockOpname(): BelongsTo
    {
        return $this->belongsTo(StockOpname::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function countedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counted_by');
    }
}