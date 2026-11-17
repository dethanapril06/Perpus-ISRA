<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EksemplarBuku extends Model
{
    use HasFactory;

    protected $table = 'eksemplar_buku';

    protected $fillable = [
        'id_buku',
        'kode_eksemplar',
        'no_inventaris',
        'id_laci',
        'kondisi',
        'status',
    ];

    public function buku(): BelongsTo
    {
        return $this->belongsTo(Buku::class, 'id_buku');
    }

    public function laci(): BelongsTo
    {
        return $this->belongsTo(Laci::class, 'id_laci');
    }

    public function peminjaman(): HasMany
    {
        return $this->hasMany(Peminjaman::class, 'id_eksemplar');
    }

    public function detailKunjunganBuku(): HasMany
    {
        return $this->hasMany(DetailKunjunganBuku::class, 'id_eksemplar');
    }

    public function isTersedia(): bool
    {
        return $this->status === 'tersedia' && $this->kondisi === 'baik';
    }
}
