<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lemari extends Model
{
    use HasFactory;

    protected $table = 'lemari';

    protected $fillable = [
        'kode_lemari',
        'nama_lokasi',
        'deskripsi',
    ];

    public function laci(): HasMany
    {
        return $this->hasMany(Laci::class, 'id_lemari');
    }
}
