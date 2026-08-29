<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadsDemo extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'leads_demos';

    protected $fillable = [
        'nama_pemohon',
        'nama_sekolah',
        'tipe_sekolah',
        'nomor_wa',
        'email',
        'estimasi_siswa',
        'model_minat',
        'url_demo_terbuat',
        'demo_expired_at',
        'status_followup',
        'catatan_sales',
    ];

    protected function casts(): array
    {
        return [
            'estimasi_siswa' => 'integer',
            'demo_expired_at' => 'datetime',
        ];
    }
}
