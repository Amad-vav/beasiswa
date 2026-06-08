<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'scholarship_id',
    'clicked_at'
])]
class ClickLog extends Model
{
    use HasFactory;

    protected $table = 'click_logs';

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'scholarship_id' => 'integer',
            'clicked_at' => 'datetime',
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
