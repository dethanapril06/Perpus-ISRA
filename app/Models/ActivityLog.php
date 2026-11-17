<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';

    protected $fillable = [
        'id_user',
        'aktivitas',
        'modul',
        'detail',
        'ip_address',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public static function log(string $aktivitas, string $modul, ?string $detail = null, ?int $userId = null): self
    {
        return self::create([
            'id_user' => $userId ?? Auth::id(),
            'aktivitas' => $aktivitas,
            'modul' => $modul,
            'detail' => $detail,
            'ip_address' => Request::ip(),
        ]);
    }
}
