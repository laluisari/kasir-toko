<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = ['id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function sales()
    {
        return $this->belongsToMany(Sale::class, 'sale_product');
    }

    public function images(): array
    {
        if (blank($this->image)) {
            return [];
        }

        return collect(preg_split('/[\s,]+/', trim($this->image)))
            ->filter()
            ->values()
            ->all();
    }
}
