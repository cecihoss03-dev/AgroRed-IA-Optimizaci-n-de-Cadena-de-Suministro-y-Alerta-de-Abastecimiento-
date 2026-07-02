<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessRoute extends Model
{
    protected $fillable = [
        'name', 'path', 'priority',
    ];

    // Relación: Una ruta puede tener varios bloqueos
    public function blockages()
    {
        return $this->hasMany(RoadBlockage::class);
    }
}