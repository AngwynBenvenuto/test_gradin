<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArduinoPortLog extends Model
{
    protected $table = 'arduino_port_logs';

    protected $fillable = [
        'arduino_port_id',
        'camera_id',
        'event_type',
        'payload',
        'raw_data',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function arduinoPort()
    {
        return $this->belongsTo(ArduinoPort::class, 'arduino_port_id');
    }

    public function camera()
    {
        return $this->belongsTo(Camera::class, 'camera_id');
    }
}
