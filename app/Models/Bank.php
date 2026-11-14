<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $table = 'bank';
    protected $primaryKey = 'id';
    protected $guarded = [];
    protected $casts = [
        'id' => 'string',
    ];

    public function company() {
        return $this->hasMany(Company::class, 'company_id', 'id');
    }
    public function coa() {
        return $this->hasMany(Coa::class, 'coa_id', 'id');
    }

    public function org() {
        return $this->hasMany(Org::class, 'org_id', 'id');
    }
}
