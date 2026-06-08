<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'scholarship_id',
    'nilai_preferensi',
    'peringkat',
    'tanggal_rekomendasi',
    'parameter_input'
])]
class Recommendation extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'scholarship_id' => 'integer',
            'nilai_preferensi' => 'float',
            'peringkat' => 'integer',
            'tanggal_rekomendasi' => 'datetime',
            'parameter_input' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class);
    }
}
