<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Dossier extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 
        'package_id', 
        'reference', 
        'title', 
        'description',
        'status',
        'assigned_to',
        'notes'
    ];

    public static function booted()
    {
        static::creating(function ($dossier) {
            if (empty($dossier->reference)) {
                // generate ELI-YYYY-XXXXXX unique reference
                $year = date('Y');
                do {
                    $rand = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                    $ref = "ELI-{$year}-{$rand}";
                } while (DB::table('dossiers')->where('reference', $ref)->exists());

                $dossier->reference = $ref;
            }
        });
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function activities()
    {
        return $this->morphMany(\Spatie\Activitylog\Models\Activity::class, 'subject');
    }

    /**
     * Scope: Get active dossiers
     */
    public function scopeActive($query)
    {
        return $query->whereIn('statut', ['en_cours', 'documents_requis', 'verification']);
    }

    /**
     * Scope: Get completed dossiers
     */
    public function scopeCompleted($query)
    {
        return $query->where('statut', 'termine');
    }

    /**
     * Scope: Get archived dossiers
     */
    public function scopeArchived($query)
    {
        return $query->where('statut', 'archive');
    }

    /**
     * Scope: Get dossiers by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('statut', $status);
    }

    /**
     * Scope: Get dossiers assigned to user
     */
    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope: Search dossiers
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('reference', 'like', "%{$search}%")
              ->orWhere('title', 'like', "%{$search}%")
              ->orWhereHas('client', function ($q) use ($search) {
                  $q->where('nom', 'like', "%{$search}%")
                    ->orWhere('prenom', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Scope: Get dossiers with all relations
     */
    public function scopeWithAllRelations($query)
    {
        return $query->with([
            'client',
            'package.documents',
            'documents',
            'assignedTo',
            'activities.causer'
        ]);
    }

    /**
     * Check if dossier is active
     */
    public function isActive(): bool
    {
        return in_array($this->statut, ['en_cours', 'documents_requis', 'verification']);
    }

    /**
     * Check if dossier is completed
     */
    public function isCompleted(): bool
    {
        return $this->statut === 'termine';
    }

    /**
     * Check if all required documents are uploaded
     */
    public function hasAllRequiredDocuments(): bool
    {
        if (!$this->package) {
            return false;
        }

        $requiredCount = $this->package->documents()->where('requis', true)->count();
        $uploadedCount = $this->documents()->count();

        return $uploadedCount >= $requiredCount;
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentageAttribute(): int
    {
        if (!$this->package) {
            return 0;
        }

        $uploadedCount = $this->documents()->count();
        $requiredCount = $this->package->documents()->where('requis', true)->count();

        return $requiredCount > 0 ? round(($uploadedCount / $requiredCount) * 100) : 0;
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->statut) {
            'en_cours' => 'blue',
            'documents_requis' => 'yellow',
            'verification' => 'orange',
            'approuve' => 'green',
            'termine' => 'green',
            'rejete' => 'red',
            'archive' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->statut) {
            'en_cours' => 'En cours',
            'documents_requis' => 'Documents requis',
            'verification' => 'En vérification',
            'approuve' => 'Approuvé',
            'termine' => 'Terminé',
            'rejete' => 'Rejeté',
            'archive' => 'Archivé',
            default => ucfirst($this->statut),
        };
    }
}
