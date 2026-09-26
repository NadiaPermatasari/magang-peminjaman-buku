<?php

namespace App\Models;

use App\Support\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinePayment extends Model
{
    use HasUuid;

    protected $fillable = ['fine_id', 'amount', 'paid_at', 'received_by', 'method', 'notes'];

    protected function casts(): array
    {
        return ['paid_at' => 'datetime'];
    }

    public function fine(): BelongsTo
    {
        return $this->belongsTo(Fine::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
