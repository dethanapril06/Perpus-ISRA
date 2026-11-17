<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailKunjunganBuku extends Model
{
    use HasFactory;

    protected $table = 'detail_kunjungan_buku';

    protected $fillable = [
        'id_kunjungan',
        'id_eksemplar',
        'waktu_scan',
        'status',
    ];

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(Kunjungan::class, 'id_kunjungan');
    }

    public function eksemplar(): BelongsTo
    {
        return $this->belongsTo(EksemplarBuku::class, 'id_eksemplar');
    }
}
