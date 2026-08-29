<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $id
 * @property string $npsn
 * @property string $nama_sekolah
 * @property string $tipe_sekolah
 * @property string|null $yayasan_induk
 * @property string $nama_pic
 * @property string $kontak_pic_wa
 * @property string $email_pic
 * @property string $provinsi
 * @property string $kabupaten_kota
 * @property string|null $alamat_lengkap
 * @property string $status_klien
 * @property-read \App\Models\Lisensi|null $active_lisensi
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Lisensi> $lisensis
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TiketDukungan> $tiketDukungans
 */
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
        /** @var Lisensi|null $lisensi */
        $lisensi = $this->lisensis()->where('status', 'active')->latest()->first();

        return $lisensi;
    }
}
