<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
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
