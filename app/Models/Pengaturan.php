<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    use HasFactory;

    protected $table = 'pengaturan';

    protected $fillable = [
        'nominal_per_hari',
        'durasi_pinjam_default',
        'maks_buku_pinjam',
        'maks_denda',
        'nama_perpustakaan',
        'alamat_perpustakaan',
        'no_telp_perpustakaan',
    ];

    protected $casts = [
        'nominal_per_hari' => 'float',
        'durasi_pinjam_default' => 'integer',
        'maks_buku_pinjam' => 'integer',
        'maks_denda' => 'float',
    ];

    public static function getPengaturan(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'nominal_per_hari' => 1000,
                'durasi_pinjam_default' => 7,
                'maks_buku_pinjam' => 3,
                'maks_denda' => null,
                'nama_perpustakaan' => 'Perpustakaan SMA Kr Indonesia Sejahtera',
                'alamat_perpustakaan' => 'Kuli Aisele, Rote Ndao, NTT',
                'no_telp_perpustakaan' => '081234567890',
            ]
        );
    }
}
