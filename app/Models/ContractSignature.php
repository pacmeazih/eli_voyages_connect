<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_id',
        'docuseal_submitter_id',
        'signer_email',
        'signer_name',
        'signer_role',
        'status',
        'sent_at',
        'opened_at',
        'signed_at',
        'ip_address',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'signed_at' => 'datetime',
    ];

    /**
     * Get the contract that owns the signature
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Check if signature is pending
     */
    public function isPending(): bool
    {
        return in_array($this->status, ['pending', 'sent', 'opened']);
    }

    /**
     * Check if signature is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'signed';
    }

    /**
     * Check if signature was declined
     */
    public function isDeclined(): bool
    {
        return $this->status === 'declined';
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'signed' => 'success',
            'declined' => 'error',
            'opened' => 'warning',
            'sent' => 'info',
            default => 'neutral',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => __('Pending'),
            'sent' => __('Sent'),
            'opened' => __('Opened'),
            'signed' => __('Signed'),
            'declined' => __('Declined'),
            default => __('Unknown'),
        };
    }
}
