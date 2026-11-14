<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CameraCaptureLog extends Model
{
    protected $table = 'camera_capture_logs';

    protected $fillable = [
        'camera_id',
        'event_type',
        'event_state',
        'event_description',
        'target_type',
        'status',
        'photo_path',
        'photo_url',
        'error_message',
        'raw_event',
        'captured_at',
    ];

    protected $casts = [
        'raw_event' => 'array',
        'captured_at' => 'datetime',
    ];
}