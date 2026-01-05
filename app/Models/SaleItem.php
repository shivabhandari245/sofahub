<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
protected $fillable = [
    'sale_id',
    'product_id',
    'quantity',
    'unit_price',
    'subtotal',
    'profit',
    'returned_quantity',
    'is_returned',
    'returned_at',
    'return_reason',
    'status',
];
    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'profit' => 'decimal:2',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(ProductModel::class);
    }
}
