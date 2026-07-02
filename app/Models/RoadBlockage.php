<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoadBlockage extends Model
{
    protected $fillable = [
        'access_route_id', 'segment_name', 'cause', 'severity', 'reported_at', 'resolved_at',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function accessRoute()
    {
        return $this->belongsTo(AccessRoute::class);
    }
}