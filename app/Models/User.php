<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['nama_lengkap', 'email', 'password', 'semester', 'ipk', 'status_akademik', 'penghasilan_ortu', 'is_premium', 'is_admin', 'last_active_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'ipk' => 'float',
            'semester' => 'integer',
            'penghasilan_ortu' => 'integer',
            'is_premium' => 'boolean',
            'is_admin' => 'boolean',
            'last_active_at' => 'datetime',
        ];
    }

    /**
     * Get the recommendations for the user.
     */
    public function recommendations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Recommendation::class);
    }

    /**
     * Get the premium subscriptions for the user.
     */
    public function premiumSubscriptions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PremiumSubscription::class);
    }
}
