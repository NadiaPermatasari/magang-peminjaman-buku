<?php

namespace App\Support\Concerns;

use Illuminate\Support\Str;

/**
 * Adds a public UUID identifier alongside the internal auto-increment id
 * (spec §7). Route model binding resolves by `uuid`, never by the
 * sequential id — UUID is a public identifier only, not an authorization
 * mechanism; routes/policies still gate access.
 */
trait HasUuid
{
    public static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
