<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BlastWa extends Model
{
    protected $table = 'blast_wa';

    protected $fillable = [
        'no_spp',
        'no_tiket',
        'kodekebun',
        'id_petani',
        'nama_petani',
        'ring',
        'no_wa_petani',
        'no_polisi',
        'nama_sopir',
        'tanggal_masuk',
        'tanggal_keluar',

        'pol',
        'brix',
        'fibre',
        'rendemen',

        'bruto',
        'tarra',
        'netto',
        'trash_persen',
        'trash',
        'berat_bersih',

        'thp_per_ton',
        'thp',
        'dibayar_hari',

        'status',
        'status_send',
        'sent_at',
        'last_send_error',
    ];

    protected $casts = [
        'tanggal_masuk' => 'datetime',
        'tanggal_keluar' => 'datetime',
        'sent_at' => 'datetime',

        'pol' => 'decimal:2',
        'brix' => 'decimal:2',
        'fibre' => 'decimal:2',
        'rendemen' => 'decimal:2',

        'bruto' => 'decimal:2',
        'tarra' => 'decimal:2',
        'netto' => 'decimal:2',
        'trash_persen' => 'decimal:2',
        'trash' => 'decimal:2',
        'berat_bersih' => 'decimal:2',

        'thp_per_ton' => 'decimal:2',
        'thp' => 'decimal:2',

        'status' => 'integer',
        'status_send' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeNotSent(Builder $query): Builder
    {
        return $query->where('status_send', 0);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isSent(): bool
    {
        return (bool) $this->status_send;
    }
}
