<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pengumumans';

    protected $fillable = [
        'judul',
        'pesan',
        'tipe',
        'target_model',
        'is_active',
        'mulai_berlaku',
        'selesai_berlaku',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'mulai_berlaku' => 'datetime',
            'selesai_berlaku' => 'datetime',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('mulai_berlaku')->orWhere('mulai_berlaku', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('selesai_berlaku')->orWhere('selesai_berlaku', '>=', now());
            });
    }
}
