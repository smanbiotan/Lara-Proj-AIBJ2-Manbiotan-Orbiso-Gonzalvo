<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * A salon customer. Guests who book get a customer record without a password;
 * registering with the same email later activates sign-in for that record.
 */
class Customer extends Authenticatable
{
    public const STATUSES = ['New', 'Active', 'Inactive'];

    protected $fillable = ['name', 'email', 'phone', 'status', 'password', 'google_id', 'avatar', 'last_login_at'];

    protected $hidden = ['password', 'remember_token', 'google_id'];

    protected function casts(): array
    {
        return ['last_login_at' => 'datetime', 'password' => 'hashed'];
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function hasAccount(): bool
    {
        return !empty($this->password) || !empty($this->google_id);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (!$term) return $query;
        return $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")
            ->orWhere('email', 'like', "%{$term}%")
            ->orWhere('phone', 'like', "%{$term}%"));
    }
}
