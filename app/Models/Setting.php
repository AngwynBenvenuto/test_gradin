<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'setting';
    protected $primaryKey = 'id';
    protected $guarded = [];

    /**
     * Config app mobile wgidriver + tracking -- lihat SettingTracking.
     */
    public function trackingApp()
    {
        return $this->belongsTo(SettingTracking::class, 'tracking_app_id');
    }
}