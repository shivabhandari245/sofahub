<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawMaterialModel extends Model
{
    use HasFactory;

    protected $table = 'rawmaterials';

    protected $fillable = [
        'name',
        'category_id',
        'supplier_id', 
        'unit_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'storage_location',
        'status',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    // ---- RELATIONSHIPS ----


public function history()
{
    return $this->hasMany(MaterialHistoryModel::class, 'raw_material_id');
}

    public function category()
    {
        return $this->belongsTo(RawMaterialCategoryModel::class, 'category_id');
    }

    public function supplier()
    {
        return $this->belongsTo(SupplierModel::class, 'supplier_id');
    }

    public function unit()
    {
        return $this->belongsTo(UnitModel::class, 'unit_id');
    }
     public function usedMaterials()
    {
        return $this->hasMany(\App\Models\UsedMaterialModel::class, 'raw_material_id');
    }
}