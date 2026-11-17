<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Laci extends Model
{
    use HasFactory;

    protected $table = 'laci';

    protected $fillable = [
        'kode_laci',
        'id_lemari',
        'deskripsi',
    ];

    public function lemari(): BelongsTo
    {
        return $this->belongsTo(Lemari::class, 'id_lemari');
    }

    public function eksemplar(): HasMany
    {
        return $this->hasMany(EksemplarBuku::class, 'id_laci');
    }

    public function buku(): HasManyThrough
    {
        return $this->hasManyThrough(
            Buku::class,
            EksemplarBuku::class,
            'id_laci',
            'id',
            'id',
            'id_buku'
        );
    }
}
