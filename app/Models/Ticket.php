<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    public const PROGRESS_LABELS = [
        0 => 'Baru Dibuat',
        1 => 'Tracking',
        2 => 'Tiba di Pabrik',
        3 => 'Core',
        4 => 'Timbangan',
        5 => 'Cane Yard / Tippler',
        6 => 'Tara',
        7 => 'Keluar Pabrik',
    ];

    protected $table = 'harvest_orders';
    protected $primaryKey = 'id';
    protected $guarded = [];
    protected $casts = [
        'id' => 'string',
        'is_cancelled' => 'boolean',
        'is_completed' => 'boolean',
        'progress' => 'integer',
    ];
}