<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'token',
        'role',
        'invited_by',
        'dossier_id',
        'expires_at',
        'accepted_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    /**
     * Generate a secure invitation token
     */
    public static function generateToken(): string
    {
        return Str::random(64);
    }

    /**
     * Create a new invitation
     */
    public static function createInvitation(string $email, string $role, int $invitedBy, ?int $dossierId = null): self
    {
        return self::create([
            'email' => $email,
            'token' => self::generateToken(),
            'role' => $role,
            'invited_by' => $invitedBy,
            'dossier_id' => $dossierId,
            'expires_at' => Carbon::now()->addDays(7),
        ]);
    }

    /**
     * Check if invitation is valid
     */
    public function isValid(): bool
    {
        return $this->accepted_at === null && $this->expires_at->isFuture();
    }

    /**
     * Mark invitation as accepted
     */
    public function markAsAccepted(): void
    {
        $this->update(['accepted_at' => Carbon::now()]);
    }

    /**
     * Relationships
     */
    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function dossier()
    {
        return $this->belongsTo(Dossier::class);
    }
}
