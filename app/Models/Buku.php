<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Buku extends Model
{
    use HasFactory;

    protected $table = 'buku';

    protected $fillable = [
        'kode_induk_buku',
        'judul',
        'pengarang',
        'penerbit',
        'tahun',
        'isbn',
        'id_kategori',
        'cover',
        'deskripsi',
    ];

    protected $casts = [
        'tahun' => 'integer',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }

    public function eksemplar(): HasMany
    {
        return $this->hasMany(EksemplarBuku::class, 'id_buku');
    }

    public function eksemplarTersedia(): HasMany
    {
        return $this->hasMany(EksemplarBuku::class, 'id_buku')->where('status', 'tersedia');
    }

    public function peminjaman(): HasManyThrough
    {
        return $this->hasManyThrough(Peminjaman::class, EksemplarBuku::class, 'id_buku', 'id_eksemplar');
    }

    public function getStokAttribute(): int
    {
        return $this->eksemplar()->where('status', 'tersedia')->count();
    }

    public function getTotalEksemplarAttribute(): int
    {
        return $this->eksemplar()->count();
    }

    public function isTersedia(): bool
    {
        return $this->stok > 0;
    }
}
