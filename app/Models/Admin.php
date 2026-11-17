<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/** Salon administrator. Authenticated through the separate "admin" guard. */
class Admin extends Authenticatable
{
    protected $fillable = ['name', 'email', 'password', 'theme', 'last_login_at'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['last_login_at' => 'datetime', 'password' => 'hashed'];
    }
}
