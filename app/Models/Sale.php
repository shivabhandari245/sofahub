<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Sale extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'datetime',
        'returned_at' => 'datetime',
        'payment_method' => 'array', // 🔹 array cast
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

    /**
     * Accessor: always decode payment_method from JSON
     */
   
 public function getPaymentMethodDisplayAttribute()
    {
        $methods = $this->payment_method;
        return !empty($methods)
            ? implode(', ', array_map(fn($m) => ucfirst($m), $methods))
            : 'N/A';
    }

    public function getPaymentStatusDisplayAttribute()
    {
        return match($this->payment_status) {
            'paid' => 'Paid',
            'unpaid' => 'Unpaid',
            'partially_paid' => 'Partially Paid',
            default => '-',
        };
    }


    public function resolveRouteBinding($value, $field = null)
    {
        if (!Auth::check()) {
            abort(404);
        }

        return $this->where('id', $value)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }
}
