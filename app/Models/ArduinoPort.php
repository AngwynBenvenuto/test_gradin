<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArduinoPort extends Model
{
    protected $table = 'arduino_ports';

    protected $fillable = [
        'name',
        'com_port',
        'baud_rate',
        'location',
        'camera_id',
        'status',
        'last_event_type',
        'last_payload',
        'last_read_at',
        'last_checked_at',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_read_at' => 'datetime',
        'last_checked_at' => 'datetime',
    ];

    public function camera()
    {
        return $this->belongsTo(Camera::class, 'camera_id');
    }
    public function logs()
    {
        return $this->hasMany(ArduinoPortLog::class, 'arduino_port_id');
    }
}