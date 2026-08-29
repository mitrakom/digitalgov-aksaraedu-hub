<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lisensi extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'lisensis';

    protected $fillable = [
        'klien_sekolah_id',
        'nomor_lisensi',
        'serial_key',
        'model_lisensi',
        'tier_paket',
        'token_api',
        'signed_license_payload',
        'domain_terdaftar',
        'hardware_fingerprint',
        'hardware_reset_count',
        'tanggal_rilis',
        'tanggal_kadaluarsa',
        'garansi_bugfix_hingga',
        'status',
        'allowed_features',
        'catatan_kontrak',
        'nilai_kontrak',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_rilis' => 'date',
            'tanggal_kadaluarsa' => 'date',
            'garansi_bugfix_hingga' => 'date',
            'allowed_features' => 'array',
            'nilai_kontrak' => 'decimal:2',
            'hardware_reset_count' => 'integer',
        ];
    }

    public function klienSekolah(): BelongsTo
    {
        return $this->belongsTo(KlienSekolah::class, 'klien_sekolah_id');
    }

    public function telemetriHeartbeats(): HasMany
    {
        return $this->hasMany(TelemetriHeartbeat::class, 'lisensi_id');
    }

    public function riwayatUpdates(): HasMany
    {
        return $this->hasMany(RiwayatUpdate::class, 'lisensi_id');
    }

    public function latestTelemetri(): ?TelemetriHeartbeat
    {
        return $this->telemetriHeartbeats()->latest('waktu_ping')->first();
    }

    public function isWarrantyActive(): bool
    {
        if (! $this->garansi_bugfix_hingga) {
            return false;
        }

        return now()->lte($this->garansi_bugfix_hingga);
    }

    public function isExpired(): bool
    {
        if ($this->model_lisensi === 'beli_putus') {
            return false;
        }

        if (! $this->tanggal_kadaluarsa) {
            return false;
        }

        return now()->gt($this->tanggal_kadaluarsa);
    }
}
