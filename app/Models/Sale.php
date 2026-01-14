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
        // Do NOT cast payment_method here
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
    public function getPaymentMethodAttribute($value)
    {
        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Accessor for DataTables / frontend display
     */
    public function getPaymentMethodDisplayAttribute()
    {
        $methods = $this->payment_method;

        if (!empty($methods)) {
            return implode(', ', array_map(fn($m) => ucfirst($m), $methods));
        }

        return 'N/A';
    }

    /**
     * Payment status display
     */
    public function getPaymentStatusDisplayAttribute()
    {
        switch ($this->payment_status) {
            case 'paid':
                return 'Paid';
            case 'unpaid':
                return 'Unpaid';
            case 'partially_paid':
                return 'Partially Paid';
            default:
                return '-';
        }
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
