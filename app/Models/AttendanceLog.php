<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    protected $table = 'attendance_log';
    protected $primaryKey = 'id';
    protected $guarded = [];
    protected $casts = [
        'id' => 'string',
    ];
}