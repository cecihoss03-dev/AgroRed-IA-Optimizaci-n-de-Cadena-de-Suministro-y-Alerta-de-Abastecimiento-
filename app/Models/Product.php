<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'category', 'unit_of_measure',
    ];

    public function listings()
    {
        return $this->hasMany(ProductListing::class);
    }

    public function priceHistories()
    {
        return $this->hasMany(PriceHistory::class);
    }

    public function pricePredictions()
    {
        return $this->hasMany(PricePrediction::class);
    }
}