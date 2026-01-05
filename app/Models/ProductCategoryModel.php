<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategoryModel extends Model
{
    protected $table = 'product_categories';

    protected $fillable = [
        'name'
    ];

    public function batches()
    {
        return $this->hasMany(BatchProductModel::class, 'productcategory_id');
    }
}
