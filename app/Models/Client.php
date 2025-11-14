<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_code',
        'civilite',
        'nom',
        'prenom',
        'first_name',
        'last_name',
        'email',
        'telephone',
        'phone',
        'adresse',
        'address',
        'city',
        'country',
        'date_naissance',
        'lieu_naissance',
        'nationalite',
        'profession',
        'passeport_numero',
        'passport_number',
        'passeport_date_emission',
        'passeport_date_expiration',
        'passport_expiry',
    ];

    protected $casts = [
        'passport_expiry' => 'datetime',
        'passeport_date_expiration' => 'date',
        'passeport_date_emission' => 'date',
        'date_naissance' => 'date',
    ];

    /**
     * Boot method to auto-generate client_code
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($client) {
            if (!$client->client_code) {
                $client->client_code = self::generateClientCode();
            }
        });
    }

    /**
     * Generate unique client code: ELV-YYYY-###
     */
    public static function generateClientCode(): string
    {
        $year = now()->year;
        $lastClient = self::where('client_code', 'like', "ELV-{$year}-%")
            ->orderBy('client_code', 'desc')
            ->first();

        if ($lastClient && preg_match('/ELV-\d{4}-(\d{3})/', $lastClient->client_code, $matches)) {
            $sequence = str_pad((int)$matches[1] + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $sequence = '001';
        }

        $code = "ELV-{$year}-{$sequence}";

        // Ensure uniqueness with invitation codes
        while (self::where('client_code', $code)->exists() || ClientInvitation::where('client_code', $code)->exists()) {
            $sequence = str_pad((int)$sequence + 1, 3, '0', STR_PAD_LEFT);
            $code = "ELV-{$year}-{$sequence}";
        }

        return $code;
    }

    /**
     * Get dossiers for this client
     */
    public function dossiers()
    {
        return $this->hasMany(Dossier::class);
    }

    /**
     * Get the user account linked to this client
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }

    /**
     * Scope: Get clients with their statistics
     */
    public function scopeWithStats($query)
    {
        return $query->withCount([
            'dossiers',
            'dossiers as active_dossiers_count' => function ($q) {
                $q->whereIn('statut', ['en_cours', 'documents_requis', 'verification']);
            },
            'dossiers as completed_dossiers_count' => function ($q) {
                $q->where('statut', 'termine');
            }
        ]);
    }

    /**
     * Scope: Get clients with active dossiers
     */
    public function scopeHasActiveDossiers($query)
    {
        return $query->whereHas('dossiers', function ($q) {
            $q->whereIn('statut', ['en_cours', 'documents_requis', 'verification']);
        });
    }

    /**
     * Get the latest dossier
     */
    public function latestDossier()
    {
        return $this->hasOne(Dossier::class)->latestOfMany();
    }

    /**
     * Get the active dossier
     */
    public function activeDossier()
    {
        return $this->hasOne(Dossier::class)
            ->whereIn('statut', ['en_cours', 'documents_requis', 'verification'])
            ->latest();
    }

    /**
     * Check if client has active dossiers
     */
    public function hasActiveDossiers(): bool
    {
        return $this->dossiers()
            ->whereIn('statut', ['en_cours', 'documents_requis', 'verification'])
            ->exists();
    }

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute(): string
    {
        return trim(($this->prenom ?? $this->first_name) . ' ' . ($this->nom ?? $this->last_name));
    }

    /**
     * Get formatted phone attribute
     */
    public function getFormattedPhoneAttribute(): ?string
    {
        $phone = $this->telephone ?? $this->phone;
        
        if (!$phone) {
            return null;
        }

        // Basic phone formatting (can be enhanced)
        return preg_replace('/(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', '$1 $2 $3 $4 $5', $phone);
    }
}
