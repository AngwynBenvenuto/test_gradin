<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gates extends Model
{
    protected $table = 'gates';
    protected $primaryKey = 'id';
    protected $guarded = [];
    protected $casts = [
        'id' => 'string',
    ];
}
