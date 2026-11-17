<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Anggota extends Model
{
    use HasFactory;

    protected $table = 'anggota';

    protected $fillable = [
        'no_anggota',
        'rfid_uid',
        'nama',
        'identitas',
        'kelas_jabatan',
        'no_hp',
        'alamat',
        'foto',
        'tgl_daftar',
        'status',
    ];

    protected $casts = [
        'tgl_daftar' => 'date',
    ];

    public function kunjungan(): HasMany
    {
        return $this->hasMany(Kunjungan::class, 'id_anggota');
    }

    public function peminjaman(): HasMany
    {
        return $this->hasMany(Peminjaman::class, 'id_anggota');
    }

    public function peminjamanAktif(): HasMany
    {
        return $this->hasMany(Peminjaman::class, 'id_anggota')->whereIn('status', ['Dipinjam', 'Terlambat']);
    }

    public function dendaBelumLunas()
    {
        return Denda::whereHas('peminjaman', function ($q) {
            $q->where('id_anggota', $this->id);
        })->where('status_bayar', 'Belum Lunas');
    }

    public function hasUnpaidFine(): bool
    {
        return $this->dendaBelumLunas()->exists();
    }

    public function scopeFindByCodeOrRfid($query, $code)
    {
        $code = trim($code);
        return $query->where(function ($q) use ($code) {
            $q->where('rfid_uid', $code)
              ->orWhere('no_anggota', $code)
              ->orWhere('identitas', $code);
        });
    }
}
