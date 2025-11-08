<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class Appointment extends Model
{
    use Notifiable;

    protected $fillable = [
        'client_id',
        'agent_id',
        'dossier_id',
        'scheduled_at',
        'duration_minutes',
        'status',
        'type',
        'notes',
        'client_notes',
        'meeting_link',
        'location',
        'reminder_sent_at',
        'confirmed_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Relationships
    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function dossier(): BelongsTo
    {
        return $this->belongsTo(Dossier::class);
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>', now())
                    ->whereIn('status', ['scheduled', 'confirmed'])
                    ->orderBy('scheduled_at');
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeForAgent($query, $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Accessors & Mutators
    public function getEndTimeAttribute()
    {
        return $this->scheduled_at->addMinutes($this->duration_minutes);
    }

    public function getIsUpcomingAttribute()
    {
        return $this->scheduled_at->isFuture() && in_array($this->status, ['scheduled', 'confirmed']);
    }

    public function getIsPastAttribute()
    {
        return $this->scheduled_at->isPast();
    }

    public function getCanBeCancelledAttribute()
    {
        return $this->is_upcoming && $this->status !== 'cancelled';
    }

    public function getCanBeRescheduledAttribute()
    {
        return $this->is_upcoming && in_array($this->status, ['scheduled', 'confirmed']);
    }

    // Methods
    public function confirm()
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    public function cancel($reason = null)
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    public function complete()
    {
        $this->update([
            'status' => 'completed',
        ]);
    }

    public function markAsNoShow()
    {
        $this->update([
            'status' => 'no_show',
        ]);
    }

    public function sendReminder()
    {
        $this->update([
            'reminder_sent_at' => now(),
        ]);
    }
}
