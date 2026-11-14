<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Org extends Model
{
    protected $table = 'org';
    protected $primaryKey = 'id';
    protected $guarded = [];
}
