<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KlienSekolah extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'klien_sekolahs';

    protected $fillable = [
        'npsn',
        'nama_sekolah',
        'tipe_sekolah',
        'yayasan_induk',
        'nama_pic',
        'kontak_pic_wa',
        'email_pic',
        'provinsi',
        'kabupaten_kota',
        'alamat_lengkap',
        'status_klien',
    ];

    public function lisensis(): HasMany
    {
        return $this->hasMany(Lisensi::class, 'klien_sekolah_id');
    }

    public function tiketDukungans(): HasMany
    {
        return $this->hasMany(TiketDukungan::class, 'klien_sekolah_id');
    }

    public function getActiveLisensiAttribute(): ?Lisensi
    {
        return $this->lisensis()->where('status', 'active')->latest()->first();
    }
}
