<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatUpdate extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'riwayat_updates';

    public $timestamps = false;

    protected $fillable = [
        'rilis_pembaruan_id',
        'lisensi_id',
        'ip_address',
        'downloaded_at',
    ];

    protected function casts(): array
    {
        return [
            'downloaded_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function rilisPembaruan(): BelongsTo
    {
        return $this->belongsTo(RilisPembaruan::class, 'rilis_pembaruan_id');
    }

    public function lisensi(): BelongsTo
    {
        return $this->belongsTo(Lisensi::class, 'lisensi_id');
    }
}
