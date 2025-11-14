<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the client associated with this user (for Client role)
     */
    public function client()
    {
        return $this->hasOne(Client::class, 'email', 'email');
    }

    /**
     * Check if user has Client role
     */
    public function isClient(): bool
    {
        return $this->hasRole('Client');
    }

    /**
     * Get active dossier for client user
     */
    public function getActiveDossier()
    {
        if (!$this->isClient() || !$this->client) {
            return null;
        }

        return $this->client->dossiers()
            ->whereIn('statut', ['en_cours', 'documents_requis', 'verification'])
            ->latest()
            ->first();
    }

    /**
     * Get all dossiers for client user
     */
    public function dossiers()
    {
        if (!$this->isClient() || !$this->client) {
            return collect([]);
        }

        return $this->client->dossiers;
    }
}
