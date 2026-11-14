<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FarmerRegistration extends Model
{
    protected $fillable = [
        'client_uuid',
        'company_code',
        'full_name',
        'nik',
        'phone',
        'bank_name',
        'bank_rek',
        'bank_account_name',
        'email',
        'gender',
        'birth_place',
        'birth_date',
        'religion',
        'marital_status',
        'address',
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
        'agreement_accepted',
        'status',
        'client_created_at',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'review_notes',

        'ktp_photo_path',
        'selfie_photo_path',
        'npwp_photo_path',
        'farm_photo_path',
        'bank_account_photo_path',
    ];

    protected $casts = [
        'land_area_ha' => 'decimal:4',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'agreement_accepted' => 'boolean',
        'client_created_at' => 'datetime',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    protected $hidden = [
        'ktp_photo_path',
        'selfie_photo_path',
        'npwp_photo_path',
        'farm_photo_path',
        'bank_account_photo_path',
    ];

    protected function nik(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => preg_replace('/\D+/', '', (string) $value),
        );
    }


    //
    public function farmer(): HasOne
    {
        return $this->hasOne(
            Farmer::class,
            'source_registration_id'
        );
    }
    public function gardenApplications(): HasMany
    {
        return $this->hasMany(
            FarmerRegistrationGarden::class
        );
    }
}
