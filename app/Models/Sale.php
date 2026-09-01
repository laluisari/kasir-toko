<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $guarded = ['id'];

    /**
     * Relationship: Sale belongs to a Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relationship: Sale belongs to a SaleDocument
     */
    public function saleDocument()
    {
        return $this->belongsTo(SaleDocument::class);
    }
}
