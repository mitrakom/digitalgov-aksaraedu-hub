<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RilisPembaruan extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'rilis_pembaruans';

    protected $fillable = [
        'nomor_versi',
        'tipe_rilis',
        'ringkasan_perubahan',
        'file_path_zip',
        'checksum_sha256',
        'file_signature',
        'minimal_versi_lms',
        'is_public',
        'is_critical_patch',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'is_critical_patch' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function riwayatUpdates(): HasMany
    {
        return $this->hasMany(RiwayatUpdate::class, 'rilis_pembaruan_id');
    }
}
