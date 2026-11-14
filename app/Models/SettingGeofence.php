<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SettingGeofence extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'latitude',
        'longitude',
        'radius',
        'stop_progress',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'double',
        'longitude' => 'double',
        'radius' => 'integer',
        'stop_progress' => 'integer',
        'is_active' => 'boolean',
    ];
}