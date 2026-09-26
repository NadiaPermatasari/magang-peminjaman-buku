<?php

namespace App\Models;

use App\Enums\MemberStatus;
use App\Support\BlindIndex;
use App\Support\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * phone/address/identity_number are stored encrypted (spec §11). Setting
 * identity_number must go through setIdentityNumber() so the blind index
 * (identity_number_index) stays in sync for uniqueness/lookup — never set
 * the identity_number attribute directly.
 */
class Member extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'user_id', 'member_number', 'name', 'email', 'phone', 'address',
        'status', 'joined_at', 'expired_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => MemberStatus::class,
            'phone' => 'encrypted',
            'address' => 'encrypted',
            'identity_number' => 'encrypted',
            'joined_at' => 'date',
            'expired_at' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function fines(): HasMany
    {
        return $this->hasMany(Fine::class);
    }

    public function setIdentityNumber(?string $value): void
    {
        $this->identity_number = $value;
        $this->identity_number_index = $value ? BlindIndex::hash($value) : null;
    }

    public static function findByIdentityNumber(string $value): ?self
    {
        return static::query()->where('identity_number_index', BlindIndex::hash($value))->first();
    }

    public function isActive(): bool
    {
        if ($this->status !== MemberStatus::ACTIVE) {
            return false;
        }

        return ! $this->expired_at || $this->expired_at->isFuture() || $this->expired_at->isToday();
    }
}
