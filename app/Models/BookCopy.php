<?php

namespace App\Models;

use App\Enums\BookCondition;
use App\Enums\BookCopyStatus;
use App\Support\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookCopy extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'book_id', 'barcode', 'inventory_code', 'acquisition_date',
        'source', 'condition', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => BookCopyStatus::class,
            'condition' => BookCondition::class,
            'acquisition_date' => 'date',
        ];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function loanItems(): HasMany
    {
        return $this->hasMany(LoanItem::class);
    }
}
