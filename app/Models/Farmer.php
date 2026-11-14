<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Farmer extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_BLOCKED = 'blocked';
    protected $table = 'farmers';
    protected $fillable = [
        'farmer_code',
        'farmer_registration_id',
        'company_code',

        'full_name',
        'nik',
        'phone',
        'gender',
        'marital_status',
        'religion',
        'birth_place',
        'birth_date',
        'email',
        'address',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'agreement_number',
        'agreement_document_path',
        'agreement_generated_at',

        'farm_name',
        'farm_address',
        'village',
        'district',
        'commodity',
        'land_ownership',
        'land_area_ha',
        'latitude',
        'longitude',
        'notes',

        'status',
        'registered_at',
        'approved_at',
    ];

    protected $casts = [
        'land_area_ha' => 'decimal:4',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'registered_at' => 'datetime',
        'approved_at' => 'datetime',
        'agreement_generated_at' => 'datetime',
    ];



    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function registration(): BelongsTo
    {
        return $this->belongsTo(
            FarmerRegistration::class,
            'farmer_registration_id'
        );
    }

    public function gardenApplications(): HasMany
    {
        return $this->hasMany(
            FarmerRegistrationGarden::class,
            'farmer_id'
        );
    }

    public function gardens(): HasMany
    {
        return $this->hasMany(
            Garden::class,
            'farmer_id'
        );
    }

    public function measurementSpks(): HasMany
    {
        return $this->hasMany(
            GardenMeasurementSpk::class,
            'farmer_id'
        );
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

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_INACTIVE => 'Tidak aktif',
            self::STATUS_BLOCKED => 'Diblokir',
            default => 'Tidak diketahui',
        };
    }
}