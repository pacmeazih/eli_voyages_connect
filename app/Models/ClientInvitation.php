<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ClientInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'client_code',
        'invitation_token',
        'status',
        'invited_by',
        'client_id',
        'user_id',
        'sent_at',
        'accepted_at',
        'expires_at',
        'metadata',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'accepted_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Boot method to auto-generate client_code and token
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invitation) {
            if (!$invitation->client_code) {
                $invitation->client_code = self::generateClientCode();
            }
            if (!$invitation->invitation_token) {
                $invitation->invitation_token = Str::random(64);
            }
            if (!$invitation->expires_at) {
                $invitation->expires_at = now()->addDays(30);
            }
        });
    }

    /**
     * Generate unique client code: EV-YYYY-XXXX
     */
    public static function generateClientCode(): string
    {
        $year = now()->year;
        $sequence = str_pad(self::where('client_code', 'like', "EV-{$year}-%")->count() + 1, 4, '0', STR_PAD_LEFT);
        
        $code = "EV-{$year}-{$sequence}";
        
        // Ensure uniqueness
        while (self::where('client_code', $code)->exists() || Client::where('client_code', $code)->exists()) {
            $sequence = str_pad((int)$sequence + 1, 4, '0', STR_PAD_LEFT);
            $code = "EV-{$year}-{$sequence}";
        }

        return $code;
    }

    /**
     * Check if invitation is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Mark invitation as sent
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Accept invitation and create client + user
     */
    public function accept(array $userData): array
    {
        if ($this->status === 'accepted') {
            throw new \Exception('Cette invitation a déjà été acceptée.');
        }

        if ($this->isExpired()) {
            throw new \Exception('Cette invitation a expiré.');
        }

        \DB::beginTransaction();
        try {
            // Create Client record
            $client = Client::create([
                'civilite' => $userData['civilite'] ?? 'M.',
                'nom' => $this->nom,
                'prenom' => $this->prenom,
                'email' => $this->email,
                'telephone' => $this->telephone,
                'client_code' => $this->client_code,
                'adresse' => $userData['adresse'] ?? null,
                'date_naissance' => $userData['date_naissance'] ?? null,
                'lieu_naissance' => $userData['lieu_naissance'] ?? null,
                'nationalite' => $userData['nationalite'] ?? null,
                'profession' => $userData['profession'] ?? null,
            ]);

            // Create User account
            $user = User::create([
                'name' => "{$this->prenom} {$this->nom}",
                'email' => $this->email,
                'password' => bcrypt($userData['password']),
                'client_id' => $client->id,
                'email_verified_at' => now(),
            ]);

            // Assign Client role
            $user->assignRole('Client');

            // Update invitation
            $this->update([
                'status' => 'accepted',
                'accepted_at' => now(),
                'client_id' => $client->id,
                'user_id' => $user->id,
            ]);

            \DB::commit();

            return [
                'client' => $client,
                'user' => $user,
            ];
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    /**
     * Relationships
     */
    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
