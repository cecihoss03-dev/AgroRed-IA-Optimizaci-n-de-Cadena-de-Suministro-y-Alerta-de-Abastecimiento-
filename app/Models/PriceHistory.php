<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceHistory extends Model
{
    protected $table = 'price_history'; // Especificamos la tabla por si Laravel busca "price_histories"

    protected $fillable = [
        'product_id', 'market_point_id', 'price', 'recorded_date', 'had_active_blockage',
    ];

    protected $casts = [
        'recorded_date' => 'date',
        'had_active_blockage' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function marketPoint()
    {
        return $this->belongsTo(MarketPoint::class);
    }
}