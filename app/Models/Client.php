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
     * Generate unique client code: EV-YYYY-XXXX
     */
    public static function generateClientCode(): string
    {
        $year = now()->year;
        $lastClient = self::where('client_code', 'like', "EV-{$year}-%")
            ->orderBy('client_code', 'desc')
            ->first();

        if ($lastClient && preg_match('/EV-\d{4}-(\d{4})/', $lastClient->client_code, $matches)) {
            $sequence = str_pad((int)$matches[1] + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $sequence = '0001';
        }

        $code = "EV-{$year}-{$sequence}";

        // Ensure uniqueness with invitation codes
        while (self::where('client_code', $code)->exists() || ClientInvitation::where('client_code', $code)->exists()) {
            $sequence = str_pad((int)$sequence + 1, 4, '0', STR_PAD_LEFT);
            $code = "EV-{$year}-{$sequence}";
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
}
