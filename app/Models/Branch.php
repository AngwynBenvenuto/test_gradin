<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $table = 'branch';
    protected $primaryKey = 'id';
    protected $guarded = [];
    protected $casts = [
        'id' => 'string',
    ];

    public function org() {
        return $this->hasMany(Org::class, 'org_id', 'id');
    }
    public function company() {
        return $this->hasMany(Company::class, 'company_id', 'id');
    }
}
