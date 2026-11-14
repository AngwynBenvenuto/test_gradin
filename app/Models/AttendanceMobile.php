<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AttendanceMobile extends Model
{
    protected $table = 'attendance_mobile';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'waktu' => 'datetime',
        'uploaded_at' => 'datetime',
        'received_at' => 'datetime',
        'nonaktif_at' => 'datetime',
        'aktif' => 'bool',
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
    ];

    public const RETENTION_MONTHS = 2;
    public const DUPLICATE_WINDOW_SECONDS = 60;
    public const DEACTIVATED_BY = 'web-absensi-mandiri (bersihkan 2 bulan)';

    public static function retentionCutoff(?Carbon $now = null): Carbon
    {
        return ($now ?? now())->copy()->subMonths(self::RETENTION_MONTHS);
    }
}
