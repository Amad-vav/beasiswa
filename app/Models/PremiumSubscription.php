<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'subscriber_type',
    'user_id',
    'institution_name',
    'paket_langganan',
    'harga_bulanan',
    'tanggal_mulai',
    'tanggal_berakhir',
    'is_active',
    'featured_scholarship_id'
])]
class PremiumSubscription extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'harga_bulanan' => 'float',
            'tanggal_mulai' => 'date',
            'tanggal_berakhir' => 'date',
            'is_active' => 'boolean',
            'featured_scholarship_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function featuredScholarship(): BelongsTo
    {
        return $this->belongsTo(Scholarship::class, 'featured_scholarship_id');
    }
}
