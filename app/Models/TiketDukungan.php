<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TiketDukungan extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'tiket_dukungans';

    protected $fillable = [
        'klien_sekolah_id',
        'nomor_tiket',
        'judul_masalah',
        'deskripsi_kendala',
        'kategori',
        'prioritas',
        'status',
        'is_garansi_claim',
        'sla_deadline',
        'tanggapan_admin',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'is_garansi_claim' => 'boolean',
            'sla_deadline' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function klienSekolah(): BelongsTo
    {
        return $this->belongsTo(KlienSekolah::class, 'klien_sekolah_id');
    }
}
