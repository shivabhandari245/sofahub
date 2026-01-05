<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\DispatchModel;
use App\Models\User;
use App\Models\SaleItem;
use App\Models\Cart;
use App\Models\Category;    
class ProductModel extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'dispatch_id',
        'name',
        'category',
        'quality',
        'user_id',
        'quantity',
        'cost_per_product',
        'total_cost',
        'source',
    
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'cost_per_product' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function dispatch()
    {
        return $this->belongsTo(DispatchModel::class, 'dispatch_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
   

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }



    

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }



public function productCategory()
{
    return $this->belongsTo(ProductCategoryModel::class, 'category'); 
    
}


public function userCategory()
{
    return $this->belongsTo(UserCategory::class, 'user_category_id');
}



}