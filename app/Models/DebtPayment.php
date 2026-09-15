<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DebtPayment extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'integer',
        'paid_at' => 'datetime',
    ];

    public function saleDocument()
    {
        return $this->belongsTo(SaleDocument::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}