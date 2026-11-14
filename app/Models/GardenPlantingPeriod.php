<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GardenPlantingPeriod extends Model
{
    protected $table = 'garden_planting_periods';
    protected $guarded = [];
    protected $casts = [
        'status' => 'integer',
        'is_active' => 'boolean',
    ];
}
