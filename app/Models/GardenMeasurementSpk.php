<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GardenMeasurementSpk extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    public const STATUS_QUEUED = 'queued';
    public const STATUS_SYNCED_TO_LAN = 'synced_to_lan';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_SURVEYING = 'surveying';
    public const STATUS_MEASUREMENT_SUBMITTED = 'measurement_submitted';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'garden_measurement_spks';

    protected $fillable = [
        'spk_number',
        'company_code',

        'farmer_id',
        'registration_garden_id',

        'revision_no',
        'status',

        'estimated_area_ha',
        'measured_area_ha',

        'lan_reference_id',

        'surveyor_id',
        'assigned_at',
        'scheduled_date',
        'assignment_notes',

        'queued_at',
        'synced_to_lan_at',
        'surveyed_at',
        'approved_at',

        'notes',
    ];

    protected $casts = [
        'farmer_id' => 'integer',
        'registration_garden_id' => 'integer',
        'revision_no' => 'integer',
        'surveyor_id' => 'integer',

        'estimated_area_ha' => 'decimal:4',
        'measured_area_ha' => 'decimal:4',

        'queued_at' => 'datetime',
        'synced_to_lan_at' => 'datetime',
        'surveyed_at' => 'datetime',
        'approved_at' => 'datetime',
        'assigned_at' => 'datetime',
        'scheduled_date' => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Farmer pemilik calon kebun.
     */
    public function farmer(): BelongsTo
    {
        return $this->belongsTo(
            Farmer::class,
            'farmer_id'
        );
    }

    /**
     * Data calon kebun yang akan diukur.
     */
    public function registrationGarden(): BelongsTo
    {
        return $this->belongsTo(
            FarmerRegistrationGarden::class,
            'registration_garden_id'
        );
    }

    /**
     * Master kebun yang terbentuk setelah hasil pengukuran disetujui.
     *
     * gardens.source_spk_id mengacu ke garden_measurement_spks.id.
     */
    public function garden(): HasOne
    {
        return $this->hasOne(
            Garden::class,
            'source_spk_id'
        );
    }

    /**
     * User yang ditugaskan untuk melakukan survei.
     */
    public function surveyor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'surveyor_id'
        );
    }

    /**
     * Seluruh survei koordinat yang berasal dari SPK ini.
     *
     * Bisa lebih dari satu karena hasil pengukuran dapat direvisi.
     */
    public function coordinateSurveys(): HasMany
    {
        return $this->hasMany(
            GardenCoordinateSurvey::class,
            'source_spk_id'
        )->orderByDesc('revision_no');
    }

    /**
     * Survei koordinat yang sedang aktif.
     */
    public function currentCoordinateSurvey(): HasOne
    {
        return $this->hasOne(
            GardenCoordinateSurvey::class,
            'source_spk_id'
        )
            ->where('is_current', true)
            ->latestOfMany();
    }

    /**
     * Survei koordinat terbaru yang sudah disetujui.
     */
    public function approvedCoordinateSurvey(): HasOne
    {
        return $this->hasOne(
            GardenCoordinateSurvey::class,
            'source_spk_id'
        )
            ->where(
                'status',
                GardenCoordinateSurvey::STATUS_APPROVED
            )
            ->latestOfMany();
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

    public function scopeForFarmer(
        Builder $query,
        int $farmerId
    ): Builder {
        return $query->where(
            'farmer_id',
            $farmerId
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

    public function scopeQueued(Builder $query): Builder
    {
        return $query->where(
            'status',
            self::STATUS_QUEUED
        );
    }

    /**
     * SPK yang belum pernah tersinkron ke LAN, terlepas dari sudah
     * sejauh mana SPK itu berjalan di alur penugasan/pengukuran
     * (sinkronisasi ke LAN adalah fitur terpisah dari alur itu).
     */
    public function scopePendingLanSync(
        Builder $query
    ): Builder {
        return $query
            ->whereNull('synced_to_lan_at')
            ->whereNotIn('status', [
                self::STATUS_COMPLETED,
                self::STATUS_CANCELLED,
                self::STATUS_REJECTED,
            ]);
    }

    public function scopeSyncedToLan(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            self::STATUS_SYNCED_TO_LAN
        );
    }

    public function scopeInProgress(
        Builder $query
    ): Builder {
        return $query->whereIn('status', [
            self::STATUS_SYNCED_TO_LAN,
            self::STATUS_SCHEDULED,
            self::STATUS_SURVEYING,
        ]);
    }

    public function scopePendingApproval(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            self::STATUS_MEASUREMENT_SUBMITTED
        );
    }

    public function scopeCompleted(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            self::STATUS_COMPLETED
        );
    }

    public function scopeUnassigned(
        Builder $query
    ): Builder {
        return $query->whereNull('surveyor_id');
    }

    public function scopeAssignedTo(
        Builder $query,
        int $surveyorId
    ): Builder {
        return $query->where('surveyor_id', $surveyorId);
    }

    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */

    public function isQueued(): bool
    {
        return $this->status === self::STATUS_QUEUED;
    }

    public function isSyncedToLan(): bool
    {
        return $this->status ===
            self::STATUS_SYNCED_TO_LAN;
    }

    public function isSurveying(): bool
    {
        return $this->status === self::STATUS_SURVEYING;
    }

    public function isMeasurementSubmitted(): bool
    {
        return $this->status ===
            self::STATUS_MEASUREMENT_SUBMITTED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Apakah SPK masih boleh ditandai tersinkron ke LAN.
     *
     * Sinkronisasi ke LAN adalah fitur terpisah dari alur penugasan/
     * pengukuran (cuma pertukaran data cloud-LAN), jadi tidak digantungkan
     * ke tahap SPK sudah sampai mana di alur itu (bisa disinkronkan kapan
     * saja sebelum SPK selesai/dibatalkan/ditolak), hanya digantungkan ke
     * apakah SPK ini sudah pernah tersinkron sebelumnya.
     */
    public function canSyncToLan(): bool
    {
        if (is_null($this->synced_to_lan_at)) {
            return !in_array(
                $this->status,
                [
                    self::STATUS_COMPLETED,
                    self::STATUS_CANCELLED,
                    self::STATUS_REJECTED,
                ],
                true
            );
        }

        return false;
    }

    /**
     * Apakah SPK sudah punya surveyor yang ditugaskan.
     */
    public function isAssigned(): bool
    {
        return !is_null($this->surveyor_id);
    }

    /**
     * Apakah SPK ini boleh ditugaskan/dijadwalkan ke surveyor.
     *
     * Penugasan surveyor tidak menunggu sinkronisasi ke LAN dulu: begitu
     * SPK terbentuk (setelah admin meng-acc pendaftaran petani), kebun
     * itu sudah siap diukur. Boleh dilakukan/diubah kapan saja selama
     * SPK belum di tahap akhir (selesai/dibatalkan/ditolak) — bukan
     * hanya di tahap awal.
     */
    public function canAssignSurveyor(): bool
    {
        return !in_array(
            $this->status,
            [
                self::STATUS_COMPLETED,
                self::STATUS_CANCELLED,
                self::STATUS_REJECTED,
            ],
            true
        );
    }

    /**
     * Apakah SPK dapat mulai disurvei.
     *
     * Sama seperti canAssignSurveyor(): tidak perlu menunggu status
     * tersinkron ke LAN. Survei juga boleh diulang berkali-kali (mis.
     * hasil ukur sebelumnya dianggap belum pas) selama kebun ini belum
     * disetujui jadi master kebun — makanya 'measurement_submitted'
     * juga termasuk boleh mulai survei lagi.
     */
    public function canStartSurvey(): bool
    {
        return in_array(
            $this->status,
            [
                self::STATUS_QUEUED,
                self::STATUS_SYNCED_TO_LAN,
                self::STATUS_SCHEDULED,
                self::STATUS_MEASUREMENT_SUBMITTED,
            ],
            true
        );
    }

    /**
     * Apakah survei SPK ini boleh diselesaikan
     * (upload tanda tangan + koordinat hasil ukur).
     */
    public function canCompleteSurvey(): bool
    {
        return $this->status === self::STATUS_SURVEYING;
    }

    /**
     * Apakah hasil pengukurannya dapat disetujui.
     */
    public function canApproveMeasurement(): bool
    {
        return $this->status ===
            self::STATUS_MEASUREMENT_SUBMITTED;
    }

    /**
     * Apakah SPK masih dapat dibatalkan.
     */
    public function canBeCancelled(): bool
    {
        return in_array(
            $this->status,
            [
                self::STATUS_QUEUED,
                self::STATUS_SYNCED_TO_LAN,
                self::STATUS_SCHEDULED,
            ],
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_QUEUED =>
                'Menunggu Penugasan',

            self::STATUS_SYNCED_TO_LAN =>
                'Terkirim ke LAN',

            self::STATUS_SCHEDULED =>
                'Dijadwalkan',

            self::STATUS_SURVEYING =>
                'Sedang diukur',

            self::STATUS_MEASUREMENT_SUBMITTED =>
                'Menunggu persetujuan hasil',

            self::STATUS_COMPLETED =>
                'Selesai',

            self::STATUS_REJECTED =>
                'Hasil ditolak',

            self::STATUS_CANCELLED =>
                'Dibatalkan',

            default =>
                'Status tidak diketahui',
        };
    }

    /**
     * Menentukan apakah hasil survei koordinat tersedia.
     */
    public function hasCoordinateSurvey(): bool
    {
        return $this->coordinateSurveys()->exists();
    }

    /**
     * Menentukan apakah master kebun sudah terbentuk.
     */
    public function hasMasterGarden(): bool
    {
        return $this->garden()->exists();
    }
}