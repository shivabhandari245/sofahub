<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsedMaterialModel extends Model
{
    protected $table = 'usedmaterials';

    protected $fillable = [
        'batchproduct_id',
        'raw_material_id',
        'quantity_used',
        'unit_cost',
        'total_cost',
        'status',
    ];

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterialModel::class, 'raw_material_id');
    }

    public function batchproduct()
    {
        return $this->belongsTo(BatchProductModel::class, 'batchproduct_id');
    }
}
