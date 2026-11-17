<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Appointment extends Model
{
    public const STATUSES = ['Pending', 'Confirmed', 'Cancelled', 'Completed', 'Rejected'];
    /** Statuses that occupy a time slot. */
    public const ACTIVE_STATUSES = ['Pending', 'Confirmed'];

    protected $fillable = [
        'reference', 'customer_id', 'service_id', 'service', 'appointment_date', 'appointment_time',
        'duration_minutes', 'status', 'notes', 'admin_notes', 'source', 'submission_token',
        'status_changed_at', 'reminder_status', 'reminder_claimed_at', 'reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date', 'status_changed_at' => 'datetime', 'reminder_claimed_at' => 'datetime',
            'reminder_sent_at' => 'datetime', 'duration_minutes' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Appointment $appointment) {
            $appointment->reference ??= static::generateReference();
        });
    }

    public static function generateReference(): string
    {
        do {
            $reference = 'BB-'.now()->format('ymd').'-'.strtoupper(Str::random(4));
        } while (static::where('reference', $reference)->exists());
        return $reference;
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function serviceRecord()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', self::ACTIVE_STATUSES);
    }

    public function getStartsAtAttribute(): Carbon
    {
        return Carbon::parse($this->appointment_date->format('Y-m-d').' '.$this->appointment_time);
    }

    public function getEndsAtAttribute(): Carbon
    {
        return $this->starts_at->copy()->addMinutes($this->duration_minutes ?: 60);
    }

    public function getTimeLabelAttribute(): string
    {
        return $this->starts_at->format('g:i A');
    }

    public function getServiceNameAttribute(): string
    {
        return $this->serviceRecord?->name ?? (string) $this->service;
    }

    /** Payload sent to n8n for appointment emails. Contains only what the email needs. */
    public function toAutomationPayload(): array
    {
        $this->loadMissing('customer', 'serviceRecord');
        return [
            'reference' => $this->reference,
            'status' => $this->status,
            'customer_name' => $this->customer?->name,
            'customer_email' => $this->customer?->email,
            'service' => $this->service_name,
            'price' => $this->serviceRecord?->price_label,
            'date' => $this->appointment_date->format('Y-m-d'),
            'date_label' => $this->appointment_date->format('l, F j, Y'),
            'time' => $this->appointment_time,
            'time_label' => $this->time_label,
            'duration_minutes' => $this->duration_minutes,
            'notes' => $this->notes,
        ];
    }
}
