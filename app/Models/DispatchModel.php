<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DispatchModel extends Model
{
    protected $table = 'dispatches';
    
    protected $fillable = [
        'batch_id',
        'user_id',
        'quantity',
        'driver',
        'status',
        'remarks',          
        'received_date',    
        'delivered_date'    
    ];

    protected $casts = [
        'quantity' => 'integer',
        'received_date' => 'datetime',
        'delivered_date' => 'datetime'
    ];

    public function batch()
    {
        return $this->belongsTo(BatchModel::class, 'batch_id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    
}