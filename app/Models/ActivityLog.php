<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Append-only audit trail (spec §24). No controller/route exposes update
 * or delete for this model — it is write-once from App\Support\Activity.
 */
class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'description',
        'subject_type',
        'subject_id',
        'subject_uuid',
        'ip_address',
        'user_agent',
        'old_values',
        'new_values',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    /**
     * Icon + color per action, used by the activity/security-dashboard views.
     * Falls back to a generic bell icon for any action not listed here.
     *
     * @var array<string, array{icon: string, color: string}>
     */
    public const PRESENTATION = [
        'LOGIN_SUCCESS' => ['icon' => 'ni ni-key-25', 'color' => 'from-blue-500 to-violet-500'],
        'LOGIN_FAILED' => ['icon' => 'ni ni-fat-remove', 'color' => 'from-red-600 to-orange-600'],
        'LOGOUT' => ['icon' => 'ni ni-button-power', 'color' => 'from-slate-600 to-slate-300'],

        '2FA_ENABLED' => ['icon' => 'ni ni-check-bold', 'color' => 'from-emerald-500 to-teal-400'],
        '2FA_DISABLED' => ['icon' => 'ni ni-fat-remove', 'color' => 'from-red-600 to-orange-600'],
        '2FA_FAILED' => ['icon' => 'ni ni-lock-circle-open', 'color' => 'from-red-600 to-orange-600'],
        'RECOVERY_CODES_REGENERATED' => ['icon' => 'ni ni-refresh-02', 'color' => 'from-orange-500 to-yellow-500'],

        'PASSWORD_CHANGED' => ['icon' => 'ni ni-lock-circle-open', 'color' => 'from-orange-500 to-yellow-500'],

        'USER_CREATED' => ['icon' => 'ni ni-fat-add', 'color' => 'from-emerald-500 to-teal-400'],
        'USER_UPDATED' => ['icon' => 'ni ni-settings', 'color' => 'from-blue-700 to-cyan-500'],
        'USER_DISABLED' => ['icon' => 'ni ni-fat-remove', 'color' => 'from-red-600 to-orange-600'],

        'ROLE_ASSIGNED' => ['icon' => 'ni ni-badge', 'color' => 'from-blue-700 to-cyan-500'],
        'ROLE_REMOVED' => ['icon' => 'ni ni-badge', 'color' => 'from-red-600 to-orange-600'],
        'PERMISSION_CHANGED' => ['icon' => 'ni ni-badge', 'color' => 'from-blue-700 to-cyan-500'],

        'BOOK_CREATED' => ['icon' => 'ni ni-book-bookmark', 'color' => 'from-emerald-500 to-teal-400'],
        'BOOK_UPDATED' => ['icon' => 'ni ni-book-bookmark', 'color' => 'from-blue-700 to-cyan-500'],
        'BOOK_DELETED' => ['icon' => 'ni ni-book-bookmark', 'color' => 'from-red-600 to-orange-600'],

        'BOOK_COPY_CREATED' => ['icon' => 'ni ni-single-copy-04', 'color' => 'from-emerald-500 to-teal-400'],
        'BOOK_COPY_UPDATED' => ['icon' => 'ni ni-single-copy-04', 'color' => 'from-blue-700 to-cyan-500'],
        'BOOK_COPY_DELETED' => ['icon' => 'ni ni-single-copy-04', 'color' => 'from-red-600 to-orange-600'],

        'CATEGORY_CREATED' => ['icon' => 'ni ni-tag', 'color' => 'from-emerald-500 to-teal-400'],
        'CATEGORY_UPDATED' => ['icon' => 'ni ni-tag', 'color' => 'from-blue-700 to-cyan-500'],
        'CATEGORY_DELETED' => ['icon' => 'ni ni-tag', 'color' => 'from-red-600 to-orange-600'],

        'AUTHOR_CREATED' => ['icon' => 'ni ni-badge', 'color' => 'from-emerald-500 to-teal-400'],
        'AUTHOR_UPDATED' => ['icon' => 'ni ni-badge', 'color' => 'from-blue-700 to-cyan-500'],
        'AUTHOR_DELETED' => ['icon' => 'ni ni-badge', 'color' => 'from-red-600 to-orange-600'],

        'PUBLISHER_CREATED' => ['icon' => 'ni ni-shop', 'color' => 'from-emerald-500 to-teal-400'],
        'PUBLISHER_UPDATED' => ['icon' => 'ni ni-shop', 'color' => 'from-blue-700 to-cyan-500'],
        'PUBLISHER_DELETED' => ['icon' => 'ni ni-shop', 'color' => 'from-red-600 to-orange-600'],

        'RACK_CREATED' => ['icon' => 'ni ni-map-big', 'color' => 'from-emerald-500 to-teal-400'],
        'RACK_UPDATED' => ['icon' => 'ni ni-map-big', 'color' => 'from-blue-700 to-cyan-500'],
        'RACK_DELETED' => ['icon' => 'ni ni-map-big', 'color' => 'from-red-600 to-orange-600'],

        'MEMBER_CREATED' => ['icon' => 'ni ni-circle-08', 'color' => 'from-emerald-500 to-teal-400'],
        'MEMBER_UPDATED' => ['icon' => 'ni ni-circle-08', 'color' => 'from-blue-700 to-cyan-500'],
        'MEMBER_DELETED' => ['icon' => 'ni ni-circle-08', 'color' => 'from-red-600 to-orange-600'],

        'LOAN_CREATED' => ['icon' => 'ni ni-cart', 'color' => 'from-blue-500 to-violet-500'],
        'LOAN_APPROVED' => ['icon' => 'ni ni-check-bold', 'color' => 'from-emerald-500 to-teal-400'],
        'LOAN_REJECTED' => ['icon' => 'ni ni-fat-remove', 'color' => 'from-red-600 to-orange-600'],
        'LOAN_HANDED_OVER' => ['icon' => 'ni ni-send', 'color' => 'from-blue-700 to-cyan-500'],
        'LOAN_RETURNED' => ['icon' => 'ni ni-check-bold', 'color' => 'from-emerald-500 to-teal-400'],
        'LOAN_CANCELLED' => ['icon' => 'ni ni-fat-remove', 'color' => 'from-slate-600 to-slate-300'],
        'LOAN_EXPIRED' => ['icon' => 'ni ni-time-alarm', 'color' => 'from-orange-500 to-yellow-500'],
        'LOAN_OVERDUE' => ['icon' => 'ni ni-time-alarm', 'color' => 'from-red-600 to-orange-600'],

        'FINE_CREATED' => ['icon' => 'ni ni-money-coins', 'color' => 'from-orange-500 to-yellow-500'],
        'FINE_MARKED_PAID' => ['icon' => 'ni ni-money-coins', 'color' => 'from-emerald-500 to-teal-400'],
        'FINE_WAIVED' => ['icon' => 'ni ni-money-coins', 'color' => 'from-slate-600 to-slate-300'],

        'SETTING_CHANGED' => ['icon' => 'ni ni-settings-gear-65', 'color' => 'from-zinc-800 to-zinc-700'],

        // Extras beyond the spec §24 minimum, used by the account/profile pages.
        'AVATAR_UPDATED' => ['icon' => 'ni ni-image', 'color' => 'from-blue-700 to-cyan-500'],
        'ACCOUNT_DELETED' => ['icon' => 'ni ni-fat-remove', 'color' => 'from-red-600 to-orange-600'],
        'SESSIONS_LOGGED_OUT' => ['icon' => 'ni ni-tablet-button', 'color' => 'from-slate-600 to-slate-300'],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function getIconAttribute(): string
    {
        return self::PRESENTATION[$this->action]['icon'] ?? 'ni ni-bell-55';
    }

    public function getColorAttribute(): string
    {
        return self::PRESENTATION[$this->action]['color'] ?? 'from-slate-600 to-slate-300';
    }

    public function getActionLabelAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', strtolower($this->action)));
    }
}
