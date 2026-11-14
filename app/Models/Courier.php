<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Courier extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'phone', 'email', 'level', 'is_active',
        'vehicle_type', 'license_plate', 'address', 'notes', 'registered_at',
    ];

    protected $casts = ['is_active' => 'boolean', 'level' => 'integer', 'registered_at' => 'datetime'];
}
