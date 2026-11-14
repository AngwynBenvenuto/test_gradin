<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GardenCoordinate extends Model
{
    public const ROLE_BOUNDARY = 'boundary';
    public const ROLE_CENTROID = 'centroid';
    public const ROLE_MARKER = 'marker';
    public const ROLE_CHECKPOINT = 'checkpoint';

    protected $table = 'garden_coordinates';

    protected $fillable = [
        'survey_id',

        'polygon_no',
        'ring_no',
        'sequence_no',

        'point_role',

        'latitude',
        'longitude',

        'altitude_m',
        'accuracy_m',

        'captured_at',
    ];

    protected $casts = [
        'survey_id' => 'integer',

        'polygon_no' => 'integer',
        'ring_no' => 'integer',
        'sequence_no' => 'integer',

        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',

        'altitude_m' => 'decimal:3',
        'accuracy_m' => 'decimal:3',

        'captured_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function survey(): BelongsTo
    {
        return $this->belongsTo(
            GardenCoordinateSurvey::class,
            'survey_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForSurvey(
        Builder $query,
        int $surveyId
    ): Builder {
        return $query->where(
            'survey_id',
            $surveyId
        );
    }

    public function scopeBoundary(Builder $query): Builder
    {
        return $query->where(
            'point_role',
            self::ROLE_BOUNDARY
        );
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('polygon_no')
            ->orderBy('ring_no')
            ->orderBy('sequence_no');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Format standar aplikasi:
     *
     * [
     *     'latitude' => -7.257763,
     *     'longitude' => 112.754016,
     * ]
     */
    public function toLatLngArray(): array
    {
        return [
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
        ];
    }

    /**
     * Format GeoJSON wajib longitude lebih dahulu.
     *
     * [
     *     112.754016,
     *     -7.257763,
     * ]
     */
    public function toGeoJsonPosition(): array
    {
        $position = [
            (float) $this->longitude,
            (float) $this->latitude,
        ];

        if ($this->altitude_m !== null) {
            $position[] = (float) $this->altitude_m;
        }

        return $position;
    }
}