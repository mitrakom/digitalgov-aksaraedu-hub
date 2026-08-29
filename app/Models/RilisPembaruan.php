<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $nomor_versi
 * @property string $tipe_rilis
 * @property string $ringkasan_perubahan
 * @property string|null $file_path_zip
 * @property string|null $checksum_sha256
 * @property string|null $file_signature
 * @property string $minimal_versi_lms
 * @property bool $is_public
 * @property bool $is_critical_patch
 * @property Carbon|null $published_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RiwayatUpdate> $riwayatUpdates
 */
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
