<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    public const STATUSES = ['New', 'Pending', 'Resolved'];

    protected $fillable = ['name', 'email', 'subject', 'message', 'status', 'source', 'admin_response', 'responded_at'];

    protected function casts(): array
    {
        return ['responded_at' => 'datetime'];
    }
}
