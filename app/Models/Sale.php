<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Sale extends Model
{
    protected $guarded = ['id', 'user_id'];

    protected $casts = [
        'payment_method' => 'array',
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

    public function getPaymentMethodAttribute($value)
    {
        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
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
