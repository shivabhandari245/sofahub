<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchProductModel extends Model
{
    protected $table = 'batch_products';

    protected $fillable = [
        'name',
        'productcategory_id',
        'productquality_id',
        'material_cost'
    ];

    // A product can be used in many batches
    public function batches()
    {
        return $this->hasMany(BatchModel::class, 'batchproduct_id');
    }

    // All materials used for 1 product (recipe)
    public function usedMaterials()
    {
        return $this->hasMany(UsedMaterialModel::class, 'batchproduct_id');
    }

    public function category()
    {
        return $this->belongsTo(ProductCategoryModel::class, 'productcategory_id');
    }

    public function quality()
    {
        return $this->belongsTo(ProductQualityModel::class, 'productquality_id');
    }

    // NEW: Delete used materials when product is deleted
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($product) {
            // This will trigger the UsedMaterialObserver::deleted() method
            $product->usedMaterials()->delete();
        });
    }
}