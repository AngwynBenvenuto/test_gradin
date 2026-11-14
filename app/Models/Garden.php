<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Garden extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_BLOCKED = 'blocked';

    public const SOURCE_CLOUD = 'CLOUD';
    public const SOURCE_LAN1 = 'LAN1';
    public const SOURCE_LAN2 = 'LAN2';

    protected $table = 'gardens';

    protected $fillable = [
        'garden_code',
        'land_type',
        'region_code',
        'section_code',
        'company_code',

        'farmer_id',
        'source_registration_garden_id',
        'source_spk_id',
        'current_coordinate_survey_id',

        'garden_name',
        'address',
        'village',
        'district',
        'commodity',
        'land_ownership',

        'area_ha',

        'status',
        'source',
        'synced_to_lan_at',
        'lan_reference_id',
        'measured_at',
        'approved_at',
    ];

    protected $casts = [
        'farmer_id' => 'integer',
        'source_registration_garden_id' => 'integer',
        'source_spk_id' => 'integer',
        'current_coordinate_survey_id' => 'integer',

        'area_ha' => 'decimal:4',

        'measured_at' => 'datetime',
        'approved_at' => 'datetime',
        'synced_to_lan_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(
            Farmer::class,
            'farmer_id'
        );
    }

    public function sourceRegistrationGarden(): BelongsTo
    {
        return $this->belongsTo(
            FarmerRegistrationGarden::class,
            'source_registration_garden_id'
        );
    }

    public function sourceSpk(): BelongsTo
    {
        return $this->belongsTo(
            GardenMeasurementSpk::class,
            'source_spk_id'
        );
    }

    /**
     * Survei koordinat yang saat ini aktif pada master kebun.
     */
    public function currentCoordinateSurvey(): BelongsTo
    {
        return $this->belongsTo(
            GardenCoordinateSurvey::class,
            'current_coordinate_survey_id'
        );
    }

    /**
     * Seluruh riwayat survei koordinat kebun.
     */
    public function coordinateSurveys(): HasMany
    {
        return $this->hasMany(
            GardenCoordinateSurvey::class,
            'garden_id'
        )->orderByDesc('revision_no');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForCompany(
        Builder $query,
        string $companyCode
    ): Builder {
        return $query->where(
            'company_code',
            strtoupper(trim($companyCode))
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_ACTIVE
        );
    }

    public function scopeForFarmer(
        Builder $query,
        int $farmerId
    ): Builder {
        return $query->where(
            'farmer_id',
            $farmerId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function hasApprovedCoordinate(): bool
    {
        return $this->current_coordinate_survey_id !== null &&
            $this->currentCoordinateSurvey?->status ===
                GardenCoordinateSurvey::STATUS_APPROVED;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function getSourceLabelAttribute(): string
    {
        return match (strtoupper((string) $this->source)) {
            self::SOURCE_CLOUD => 'Cloud',
            self::SOURCE_LAN1 => 'LAN 1',
            self::SOURCE_LAN2 => 'LAN 2',
            default => (string) $this->source,
        };
    }
}