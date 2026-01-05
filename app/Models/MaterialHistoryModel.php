<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialHistoryModel extends Model
{
    protected $table = 'material_histories';
    
    protected $fillable = [
        'raw_material_id',
        'old_quantity',
        'quantity_change',
        'new_quantity',
        'old_unit_cost',
        'unit_cost',
        'total_cost_change',
        'type',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'old_quantity' => 'integer',
        'quantity_change' => 'integer',
        'new_quantity' => 'integer',
        'old_unit_cost' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost_change' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function material()
    {
        return $this->belongsTo(RawMaterialModel::class, 'raw_material_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFormattedTypeAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->type));
    }

    public function getTypeColorAttribute()
    {
        return [
            'initial_stock' => 'info',
            'restocked' => 'success',
            'used' => 'warning'
        ][$this->type] ?? 'secondary';
    }
}