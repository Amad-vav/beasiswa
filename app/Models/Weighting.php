<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'criteria_id',
    'bobot',
    'versi_bobot',
    'berlaku_dari',
    'ditetapkan_oleh'
])]
class Weighting extends Model
{
    use HasFactory;

    protected $table = 'weighting';

    protected function casts(): array
    {
        return [
            'criteria_id' => 'integer',
            'bobot' => 'float',
            'versi_bobot' => 'integer',
            'berlaku_dari' => 'date',
        ];
    }

    public function criteria(): BelongsTo
    {
        return $this->belongsTo(Criteria::class, 'criteria_id');
    }
}
