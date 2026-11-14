<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GardenCoordinateSurvey extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    public const STATUS_DRAFT = 0;
    public const STATUS_SUBMITTED = 1;
    public const STATUS_APPROVED = 2;
    public const STATUS_REJECTED = 3;
    public const STATUS_SUPERSEDED = 4;
    public const STATUS_CANCELLED = 5;

    /*
    |--------------------------------------------------------------------------
    | Source
    |--------------------------------------------------------------------------
    */

    public const SOURCE_LAN = 'LAN';
    public const SOURCE_DRONE = 'DRONE';
    public const SOURCE_MOBILE = 'MOBILE';
    public const SOURCE_GPS = 'GPS';
    public const SOURCE_RTK = 'RTK';
    public const SOURCE_IMPORT = 'IMPORT';
    public const SOURCE_ADMIN = 'ADMIN';

    /*
    |--------------------------------------------------------------------------
    | Capture Method
    |--------------------------------------------------------------------------
    */

    public const CAPTURE_DRONE = 'drone';
    public const CAPTURE_GPS_WALK = 'gps_walk';
    public const CAPTURE_RTK = 'rtk';
    public const CAPTURE_MANUAL = 'manual';
    public const CAPTURE_IMPORT = 'import';

    protected $table = 'garden_coordinate_surveys';

    protected $fillable = [
        'survey_uuid',

        'registration_garden_id',
        'garden_id',
        'source_spk_id',

        'company_code',

        'revision_no',
        'previous_survey_id',

        'source',
        'source_reference_id',

        'status',
        'is_current',

        'calculated_area_ha',
        'centroid_latitude',
        'centroid_longitude',
        'accuracy_m',

        'capture_method',

        'revision_reason',
        'review_note',

        'raw_file_path',
        'coordinate_hash',

        'signature_path',
        'signed_by_name',
        'signed_at',

        'surveyed_at',
        'synced_at',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'registration_garden_id' => 'integer',
        'garden_id' => 'integer',
        'source_spk_id' => 'integer',

        'revision_no' => 'integer',
        'previous_survey_id' => 'integer',

        'status' => 'integer',
        'is_current' => 'boolean',

        'calculated_area_ha' => 'decimal:4',
        'centroid_latitude' => 'decimal:8',
        'centroid_longitude' => 'decimal:8',
        'accuracy_m' => 'decimal:3',

        'surveyed_at' => 'datetime',
        'synced_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'signed_at' => 'datetime',
    ];

    /*
     * current_registration_key adalah generated column,
     * jadi tidak dimasukkan ke fillable.
     */

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function registrationGarden(): BelongsTo
    {
        return $this->belongsTo(
            FarmerRegistrationGarden::class,
            'registration_garden_id'
        );
    }

    /**
     * Akan terisi setelah calon kebun menjadi master kebun.
     */
    public function garden(): BelongsTo
    {
        return $this->belongsTo(
            Garden::class,
            'garden_id'
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
     * Survei sebelumnya yang digantikan oleh survei ini.
     */
    public function previousSurvey(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'previous_survey_id'
        );
    }

    /**
     * Daftar survei ulang yang dibuat dari survei ini.
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(
            self::class,
            'previous_survey_id'
        )->orderBy('revision_no');
    }

    /**
     * Seluruh titik koordinat dalam survei.
     */
    public function coordinates(): HasMany
    {
        return $this->hasMany(
            GardenCoordinate::class,
            'survey_id'
        )->orderBy('polygon_no')
            ->orderBy('ring_no')
            ->orderBy('sequence_no');
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

    public function scopeForGarden(
        Builder $query,
        int $gardenId
    ): Builder {
        return $query->where(
            'garden_id',
            $gardenId
        );
    }

    public function scopeForRegistrationGarden(
        Builder $query,
        int $registrationGardenId
    ): Builder {
        return $query->where(
            'registration_garden_id',
            $registrationGardenId
        );
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_APPROVED
        );
    }

    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_SUBMITTED
        );
    }

    public function scopePendingReview(Builder $query): Builder
    {
        return $query->whereIn('status', [
            self::STATUS_DRAFT,
            self::STATUS_SUBMITTED,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isSubmitted(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isSuperseded(): bool
    {
        return $this->status === self::STATUS_SUPERSEDED;
    }

    public function canBeApproved(): bool
    {
        return in_array(
            $this->status,
            [
                self::STATUS_DRAFT,
                self::STATUS_SUBMITTED,
            ],
            true
        );
    }

    public function hasSignature(): bool
    {
        return filled($this->signature_path);
    }

    public function canBeRevised(): bool
    {
        return in_array(
            $this->status,
            [
                self::STATUS_APPROVED,
                self::STATUS_REJECTED,
                self::STATUS_SUPERSEDED,
            ],
            true
        );
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Menunggu persetujuan',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_SUPERSEDED => 'Digantikan',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => 'Tidak diketahui',
        };
    }

    public function getSourceLabelAttribute(): string
    {
        return match (strtoupper($this->source)) {
            self::SOURCE_LAN => 'LAN',
            self::SOURCE_DRONE => 'Drone',
            self::SOURCE_MOBILE => 'Aplikasi Mobile',
            self::SOURCE_GPS => 'GPS',
            self::SOURCE_RTK => 'RTK',
            self::SOURCE_IMPORT => 'Import',
            self::SOURCE_ADMIN => 'Input Admin',
            default => $this->source,
        };
    }
}