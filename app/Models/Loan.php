<?php

namespace App\Models;

use App\Enums\LoanStatus;
use App\Support\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasUuid;

    protected $fillable = [
        'code', 'member_id', 'status', 'requested_at',
        'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'rejection_reason',
        'pickup_deadline', 'borrowed_at', 'due_at', 'returned_at', 'cancelled_at', 'expired_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => LoanStatus::class,
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'pickup_deadline' => 'datetime',
            'borrowed_at' => 'datetime',
            'due_at' => 'datetime',
            'returned_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'expired_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(LoanItem::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}
