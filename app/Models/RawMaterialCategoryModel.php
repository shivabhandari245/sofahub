<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawMaterialCategoryModel extends Model
{
    use HasFactory;

    protected $table = 'rawmaterialcategories';

    protected $fillable = [
        'name',
    ];


    public function rawMaterials()
    {
        return $this->hasMany(RawMaterialModel::class, 'category_id');
    }

    
}
