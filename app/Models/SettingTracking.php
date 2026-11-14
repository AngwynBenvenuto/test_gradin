<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Config app mobile wgidriver (update/force-update) + tracking (radius,
 * interval, toleransi area, dst.) -- dipisah dari tabel `setting` utama
 * (lihat migrasi 2026_10_04_000002_create_setting_tracking_table) supaya
 * nambah/ubah field config ini tidak perlu bongkar-pasang tabel `setting`.
 * Relasi: `setting.tracking_app_id` -> `setting_tracking.id`.
 */
class SettingTracking extends Model
{
    protected $table = 'setting_tracking';
    protected $guarded = [];

    protected $casts = [
        'tracking_area_tolerance_rules' => 'array',
        'tracking_gps_tolerance_rules' => 'array',
    ];
}
