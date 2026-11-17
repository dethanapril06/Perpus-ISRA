<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kunjungan extends Model
{
    use HasFactory;

    protected $table = 'kunjungan';

    protected $fillable = [
        'id_anggota',
        'tanggal',
        'waktu_masuk',
        'waktu_keluar',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class, 'id_anggota');
    }

    public function detailBuku(): HasMany
    {
        return $this->hasMany(DetailKunjunganBuku::class, 'id_kunjungan');
    }

    public function peminjaman(): HasMany
    {
        return $this->hasMany(Peminjaman::class, 'id_kunjungan');
    }
}
