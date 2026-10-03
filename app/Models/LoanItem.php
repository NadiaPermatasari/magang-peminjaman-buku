<?php

namespace App\Models;

use App\Enums\BookCondition;
use App\Enums\LoanStatus;
use App\Support\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class LoanItem extends Model
{
    use HasUuid;

    protected $fillable = [
        'loan_id', 'book_id', 'book_copy_id', 'status',
        'borrowed_at', 'due_at', 'returned_at',
        'condition_on_borrow', 'condition_on_return',
        'handover_photo_path', 'return_photo_path', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => LoanStatus::class,
            'condition_on_borrow' => BookCondition::class,
            'condition_on_return' => BookCondition::class,
            'borrowed_at' => 'datetime',
            'due_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function bookCopy(): BelongsTo
    {
        return $this->belongsTo(BookCopy::class);
    }

    /** URL bukti foto serah terima buku ke anggota (null bila belum diunggah). */
    public function getHandoverPhotoUrlAttribute(): ?string
    {
        return $this->handover_photo_path ? Storage::disk('public')->url($this->handover_photo_path) : null;
    }

    /** URL bukti foto pengembalian buku (null bila belum diunggah). */
    public function getReturnPhotoUrlAttribute(): ?string
    {
        return $this->return_photo_path ? Storage::disk('public')->url($this->return_photo_path) : null;
    }
}
