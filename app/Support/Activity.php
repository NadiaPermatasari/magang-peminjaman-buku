<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Append-only audit logger (spec §24).
 *
 *   Activity::log('LOAN_APPROVED', "Approved loan {$loan->code}", $loan);
 *   Activity::log('FINE_WAIVED', "Waived fine", $fine, values: ['before' => [...], 'after' => [...]]);
 *
 * Never pass password/OTP/2FA secret/recovery code/token values in
 * $description or $values — those must never reach the audit log.
 */
class Activity
{
    /**
     * @param  array{before?: array<string, mixed>, after?: array<string, mixed>}|null  $values
     */
    public static function log(string $action, string $description, ?Model $subject = null, ?User $user = null, ?array $values = null): ActivityLog
    {
        $request = request();

        return ActivityLog::create([
            'user_id' => $user?->id ?? auth()->id(),
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'subject_uuid' => $subject?->uuid ?? null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? substr((string) $request->userAgent(), 0, 500) : null,
            'old_values' => $values['before'] ?? null,
            'new_values' => $values['after'] ?? null,
        ]);
    }
}
