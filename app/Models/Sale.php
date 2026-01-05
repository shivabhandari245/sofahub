<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
    'user_id',
    'customer_id',
    'subtotal',
    'tax_rate',      
    'tax_amount',    
    'discount',
    'total_amount',
    'profit',
    'status'
];




    protected $casts = [
    'date' => 'datetime',
    'returned_at' => 'datetime',
];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    
    public function totalQuantity()
    {
        return $this->items()->sum('quantity');
    }
}
