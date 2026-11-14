<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Camera extends Model
{
    protected $table = 'cameras';
    protected $primaryKey = 'id';
    protected $guarded = [];
    protected $casts = [
        'id' => 'string',
        'last_event_at' => 'datetime',
        'connection_status' => 'string',
        'last_checked_at' => 'datetime',
    ];


    public function arduinoPort()
    {
        return $this->hasOne(ArduinoPort::class, 'camera_id');
    }
    public function captureLogs()
    {
        return $this->hasMany(CameraCaptureLog::class, 'camera_id');
    }
}
