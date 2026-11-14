<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipplerTransaction extends Model
{
    protected $table = 'tippler_transactions';

    protected $fillable = [
        'local_id',
        'camera_id',
        'arduino_port_id',
        'station_id',
        'station_label',
        'no_tiket',
        'kode_kebun',
        'no_polisi',
        'jenis_truk',
        'source',
        'trash_percentage',
        'trash_percentage2',
        'photo_1',
        'photo_2',
        'photo_3',
        'status',
        'saved_at',
    ];

    protected $casts = [
        'trash_percentage' => 'decimal:2',
        'trash_percentage2' => 'decimal:2',
        'saved_at' => 'datetime',
    ];

    public function camera()
    {
        return $this->belongsTo(Camera::class, 'camera_id');
    }

    public function arduinoPort()
    {
        return $this->belongsTo(ArduinoPort::class, 'arduino_port_id');
    }
}
