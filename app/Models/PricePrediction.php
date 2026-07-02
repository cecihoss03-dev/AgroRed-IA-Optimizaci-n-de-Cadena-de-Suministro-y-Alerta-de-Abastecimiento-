<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricePrediction extends Model
{
    protected $fillable = [
        'product_id', 'market_point_id', 'predicted_price', 'confidence_score', 
        'scenario_input', 'model_explanation', 'valid_for_date',
    ];

    // IMPORTANTE: Laravel convierte automáticamente los JSON a arrays gracias a esto
    protected $casts = [
        'scenario_input' => 'array',
        'model_explanation' => 'array',
        'valid_for_date' => 'date',
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