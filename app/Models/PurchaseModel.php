<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseModel extends Model
{
    use HasFactory;

    protected $table = 'purchases';

    protected $fillable = [
        'user_id',
        'product_name',
        'category',
        'supplier_name',
        'supplier_contact',
        'quantity',
        'unit_cost',
        'totalcost',
        'quality',
        'delivery_date',
        'status',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'float',
        'delivery_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
