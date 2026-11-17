<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjaman';

    protected $fillable = [
        'id_kunjungan',
        'id_anggota',
        'id_eksemplar',
        'id_user',
        'tgl_pinjam',
        'tgl_jatuh_tempo',
        'tgl_kembali',
        'status',
        'kondisi_kembali',
        'catatan',
    ];

    protected $casts = [
        'tgl_pinjam' => 'date',
        'tgl_jatuh_tempo' => 'date',
        'tgl_kembali' => 'date',
    ];

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(Kunjungan::class, 'id_kunjungan');
    }

    public function anggota(): BelongsTo
    {
        return $this->belongsTo(Anggota::class, 'id_anggota');
    }

    public function eksemplar(): BelongsTo
    {
        return $this->belongsTo(EksemplarBuku::class, 'id_eksemplar');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function denda(): HasOne
    {
        return $this->hasOne(Denda::class, 'id_peminjaman');
    }

    // Helper to get parent book title directly
    public function getBukuAttribute()
    {
        return $this->eksemplar ? $this->eksemplar->buku : null;
    }

    public function hitungHariTerlambat($tanggalKembali = null): int
    {
        $kembali = $tanggalKembali ? Carbon::parse($tanggalKembali)->startOfDay() : Carbon::today();
        $jatuhTempo = Carbon::parse($this->tgl_jatuh_tempo)->startOfDay();

        if ($kembali->gt($jatuhTempo)) {
            return (int) $jatuhTempo->diffInDays($kembali);
        }

        return 0;
    }
}
