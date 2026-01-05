<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductQualityModel extends Model
{
    protected $table = 'product_quality';

    protected $fillable = [
        'name'
    ];

    public function batches()
    {
        return $this->hasMany(BatchProductModel::class, 'productquality_id');
    }
}
