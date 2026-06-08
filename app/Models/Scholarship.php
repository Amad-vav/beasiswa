<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'nama_beasiswa',
    'url_tautan',
    'penyelenggara',
    'deskripsi',
    'semester_min',
    'semester_max',
    'ipk_minimum',
    'batas_penghasilan',
    'skor_status_c2',
    'batas_waktu',
    'jumlah_klik',
    'is_featured',
    'status_aktif'
])]
class Scholarship extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'semester_min' => 'integer',
            'semester_max' => 'integer',
            'ipk_minimum' => 'float',
            'batas_penghasilan' => 'integer',
            'skor_status_c2' => 'integer',
            'batas_waktu' => 'datetime',
            'jumlah_klik' => 'integer',
            'is_featured' => 'boolean',
            'status_aktif' => 'boolean',
        ];
    }

    public function clickLogs(): HasMany
    {
        return $this->hasMany(ClickLog::class);
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class);
    }

    public function premiumSubscriptions(): HasMany
    {
        return $this->hasMany(PremiumSubscription::class, 'featured_scholarship_id');
    }
}
