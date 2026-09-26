<?php

namespace App\Models;

use App\Enums\FineStatus;
use App\Support\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fine extends Model
{
    use HasUuid;

    protected $fillable = [
        'loan_item_id', 'member_id', 'type', 'late_days', 'rate', 'amount',
        'status', 'calculated_at', 'paid_at', 'waived_at', 'waived_by', 'waive_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => FineStatus::class,
            'calculated_at' => 'datetime',
            'paid_at' => 'datetime',
            'waived_at' => 'datetime',
        ];
    }

    public function loanItem(): BelongsTo
    {
        return $this->belongsTo(LoanItem::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function waivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'waived_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FinePayment::class);
    }
}
