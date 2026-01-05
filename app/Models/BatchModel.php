<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchModel extends Model
{
    use HasFactory;

    protected $table = 'batches';

    protected $fillable = [
        'batchproduct_id',
        'leader_name',
        'quantity',
        'labor_cost',
        'other_expenses',
        'expected_unit_cost',
        'total_cost',
        'start_date',
        'expected_completion',
        'status',
    ];


    public function product()
    {
        return $this->belongsTo(BatchProductModel::class, 'batchproduct_id');
    }

    
   
}
