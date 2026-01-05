<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitModel extends Model
{
    use HasFactory;

    protected $table = 'units';

    protected $fillable = [
        'name',
    ];

    public function rawMaterials()
    {
        return $this->hasMany(RawMaterialModel::class, 'unit_id');
    }
}
