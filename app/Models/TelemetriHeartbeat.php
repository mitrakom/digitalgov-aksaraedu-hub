<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelemetriHeartbeat extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'telemetri_heartbeats';

    public $timestamps = false;

    protected $fillable = [
        'lisensi_id',
        'ip_address',
        'domain_terdeteksi',
        'versi_lms',
        'versi_php',
        'total_siswa_aktif',
        'total_guru_aktif',
        'total_rombel_aktif',
        'total_ujian_cbt',
        'db_size_mb',
        'waktu_ping',
    ];

    protected function casts(): array
    {
        return [
            'total_siswa_aktif' => 'integer',
            'total_guru_aktif' => 'integer',
            'total_rombel_aktif' => 'integer',
            'total_ujian_cbt' => 'integer',
            'db_size_mb' => 'decimal:2',
            'waktu_ping' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function lisensi(): BelongsTo
    {
        return $this->belongsTo(Lisensi::class, 'lisensi_id');
    }
}
