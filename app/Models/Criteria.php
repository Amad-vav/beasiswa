<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'kode_kriteria',
    'nama_kriteria',
    'dimensi',
    'tipe_kriteria',
    'deskripsi'
])]
class Criteria extends Model
{
    use HasFactory;

    protected $table = 'criteria';

    public function weightings(): HasMany
    {
        return $this->hasMany(Weighting::class, 'criteria_id');
    }
}
