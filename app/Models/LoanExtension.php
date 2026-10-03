<?php

namespace App\Models;

use App\Enums\ExtensionStatus;
use App\Support\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pengajuan perpanjangan (banding) satu peminjaman. Keputusan admin dicatat
 * di decided_by/decided_at beserta pergeseran jatuh temponya.
 */
class LoanExtension extends Model
{
    use HasUuid;

    protected $fillable = [
        'loan_id', 'requested_by', 'days', 'reason', 'status', 'requested_at',
        'decided_by', 'decided_at', 'decision_note', 'previous_due_at', 'new_due_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ExtensionStatus::class,
            'days' => 'integer',
            'requested_at' => 'datetime',
            'decided_at' => 'datetime',
            'previous_due_at' => 'datetime',
            'new_due_at' => 'datetime',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function isPending(): bool
    {
        return $this->status === ExtensionStatus::PENDING;
    }
}
