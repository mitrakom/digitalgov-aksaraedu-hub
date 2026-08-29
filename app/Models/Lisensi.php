<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $klien_sekolah_id
 * @property string $nomor_lisensi
 * @property string $serial_key
 * @property string $model_lisensi
 * @property string $tier_paket
 * @property string $token_api
 * @property string|null $signed_license_payload
 * @property string|null $domain_terdaftar
 * @property string|null $hardware_fingerprint
 * @property int $hardware_reset_count
 * @property Carbon|null $tanggal_rilis
 * @property Carbon|null $tanggal_kadaluarsa
 * @property Carbon|null $garansi_bugfix_hingga
 * @property string $status
 * @property array<string>|null $allowed_features
 * @property string|null $catatan_kontrak
 * @property float $nilai_kontrak
 * @property-read KlienSekolah $klienSekolah
 * @property-read Collection<int, TelemetriHeartbeat> $telemetriHeartbeats
 * @property-read Collection<int, RiwayatUpdate> $riwayatUpdates
 */
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
        /** @var TelemetriHeartbeat|null $telemetri */
        $telemetri = $this->telemetriHeartbeats()->latest('waktu_ping')->first();

        return $telemetri;
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
