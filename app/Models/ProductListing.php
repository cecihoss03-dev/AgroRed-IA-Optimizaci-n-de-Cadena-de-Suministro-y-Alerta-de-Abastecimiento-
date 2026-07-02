<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductListing extends Model
{
    protected $fillable = [
        'product_id', 'production_center_id', 'available_stock', 'current_price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productionCenter()
    {
        return $this->belongsTo(ProductionCenter::class);
    }
}