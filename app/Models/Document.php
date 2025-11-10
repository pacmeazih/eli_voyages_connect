<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Document extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'dossier_id',
        'name',
        'type',
        'status',
        'path',
        'size',
        'mime_type',
        'description',
        'uploaded_by',
        'consultant_id',
        'assigned_by',
        'consultant_signed_at',
        'approval_status',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'previous_version_id',
        'version',
        'docuseal_submission_id',
        'docuseal_template_id',
        'docuseal_signers',
        'sent_for_signature_at',
        'completed_at',
        'signed_document_path',
    ];

    protected $casts = [
        'size' => 'integer',
        'version' => 'integer',
        'docuseal_signers' => 'array',
        'sent_for_signature_at' => 'datetime',
        'completed_at' => 'datetime',
        'consultant_signed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * Activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'type', 'path', 'size'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Relationships
     */
    public function dossier()
    {
        return $this->belongsTo(Dossier::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function consultant()
    {
        return $this->belongsTo(User::class, 'consultant_id');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function previousVersion()
    {
        return $this->belongsTo(Document::class, 'previous_version_id');
    }

    public function nextVersions()
    {
        return $this->hasMany(Document::class, 'previous_version_id');
    }

    /**
     * Scopes
     */
    public function scopeLatestVersions($query)
    {
        return $query->whereDoesntHave('nextVersions');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Accessors
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * DocuSeal related helpers
     */
    public function isSentForSignature(): bool
    {
        return $this->status === 'sent';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Approval workflow helpers
     */
    public function isPending(): bool
    {
        return $this->approval_status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected';
    }

    public function approve(User $approver): void
    {
        $this->update([
            'approval_status' => 'approved',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    public function reject(User $approver, string $reason): void
    {
        $this->update([
            'approval_status' => 'rejected',
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    public function markCompleted(string $signedPath = null): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'signed_document_path' => $signedPath ?? $this->signed_document_path,
        ]);
    }
}

