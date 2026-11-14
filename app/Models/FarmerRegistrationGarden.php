<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FarmerRegistrationGarden extends Model
{
    protected $fillable = [
        'farmer_registration_id',
        'farmer_id',
        'client_uuid',
        'company_code',
        'farm_name',
        'farm_address',
        'village',
        'district',
        'commodity',
        'land_ownership',
        'estimated_area_ha',
        'estimated_latitude',
        'estimated_longitude',
        'notes',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'estimated_area_ha' => 'decimal:4',
        'estimated_latitude' => 'decimal:8',
        'estimated_longitude' => 'decimal:8',
        'submitted_at' => 'datetime',
    ];



    //
    public function registration(): BelongsTo
    {
        return $this->belongsTo(
            FarmerRegistration::class,
            'farmer_registration_id'
        );
    }
    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }
    public function measurementSpks(): HasMany
    {
        return $this->hasMany(
            GardenMeasurementSpk::class,
            'registration_garden_id'
        )->orderByDesc('revision_no');
    }
    public function latestMeasurementSpk(): HasOne
    {
        return $this->hasOne(
            GardenMeasurementSpk::class,
            'registration_garden_id'
        )->latestOfMany();
    }

    public function garden(): HasOne
    {
        return $this->hasOne(
            Garden::class,
            'source_registration_garden_id'
        );
    }

    public function coordinateSurveys(): HasMany
    {
        return $this->hasMany(
            GardenCoordinateSurvey::class,
            'registration_garden_id'
        )->orderByDesc('revision_no');
    }
}