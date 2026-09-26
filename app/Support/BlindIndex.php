<?php

namespace App\Support;

/**
 * Keyed-HMAC blind index for encrypted-but-searchable fields (spec §11) —
 * e.g. member identity_number needs a uniqueness check without storing the
 * NIK in a directly searchable/guessable form. Never use plain SHA-256 for
 * this: HMAC with a secret key (BLIND_INDEX_KEY, distinct from APP_KEY)
 * prevents an attacker who only has the database from brute-forcing values.
 */
class BlindIndex
{
    public static function hash(string $value): string
    {
        $key = config('app.blind_index_key');

        if (! $key) {
            throw new \RuntimeException('BLIND_INDEX_KEY is not configured.');
        }

        return hash_hmac('sha256', self::normalize($value), $key);
    }

    private static function normalize(string $value): string
    {
        return mb_strtolower(trim($value));
    }
}
