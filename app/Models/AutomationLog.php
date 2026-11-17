<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutomationLog extends Model
{
    public const STATUS_LABELS = [
        'pending' => 'Pending',
        'sent_to_n8n' => 'Sent to n8n',
        'skipped' => 'Skipped (not configured)',
        'failed' => 'Failed',
        'duplicate' => 'Duplicate ignored',
        'email_sent' => 'Email sent (reported by n8n)',
        'email_failed' => 'Email failed (reported by n8n)',
        'success' => 'Success',
        'fallback' => 'Answered by fallback',
    ];

    protected $fillable = [
        'event_id', 'workflow', 'event', 'idempotency_key', 'appointment_id', 'appointment_reference',
        'status', 'http_status', 'payload', 'message', 'completed_at',
    ];

    protected function casts(): array
    {
        return ['payload' => 'array', 'completed_at' => 'datetime'];
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }
}
