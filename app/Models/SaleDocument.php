<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleDocument extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
