<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Buyer extends Model
{
    protected $fillable = [
        'name',
        'phone',
    ];

    public function saleDocuments()
    {
        return $this->hasMany(SaleDocument::class);
    }
}
