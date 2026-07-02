<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionCenter extends Model
{
    protected $fillable = [
        'name', 'producer_id', 'municipality', 'location',
    ];

    // Relación: Pertenece a un productor (usuario)
    public function producer()
    {
        return $this->belongsTo(User::class, 'producer_id');
    }

    // Relación: Tiene muchas ofertas de productos
    public function listings()
    {
        return $this->hasMany(ProductListing::class);
    }
}