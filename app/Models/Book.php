<?php

namespace App\Models;

use App\Enums\BookCopyStatus;
use App\Support\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Book extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'no_hak', 'no_su', 'nama_pemilik', 'title', 'slug', 'category_id',
        'publication_year', 'description', 'cover_path', 'is_active',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function copies(): HasMany
    {
        return $this->hasMany(BookCopy::class);
    }

    /**
     * Slug dibuat otomatis dari judul; diberi akhiran -2, -3, ... bila sudah dipakai.
     */
    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'buku';
        $slug = $base;

        for ($i = 2; static::withTrashed()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover_path ? Storage::disk('public')->url($this->cover_path) : null;
    }

    public function getAvailableCopiesCountAttribute(): int
    {
        return $this->copies->where('status', BookCopyStatus::AVAILABLE)->count();
    }
}
