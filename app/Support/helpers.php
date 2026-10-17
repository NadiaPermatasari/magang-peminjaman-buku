<?php

use App\Models\Setting;

if (! function_exists('setting')) {
    /**
     * Read an application setting saved from the Settings page.
     *
     *   setting('app_name')              -> value or default
     *   setting('per_page', 15)          -> value or 15
     */
    function setting(string $key, mixed $default = null): mixed
    {
        try {
            return Setting::get($key, $default);
        } catch (Throwable) {
            // Before migrations run (or without a database) fall back to defaults.
            return $default ?? Setting::$defaults[$key] ?? null;
        }
    }
}

if (! function_exists('max_active_loans')) {
    /**
     * Jumlah buku yang boleh dipinjam sekaligus oleh satu anggota (1–2).
     * Nilai lama di luar rentang itu otomatis dipotong.
     */
    function max_active_loans(): int
    {
        return max(1, min(Setting::MAX_ACTIVE_LOANS_LIMIT, (int) setting('max_active_loans')));
    }
}

if (! function_exists('app_name')) {
    /**
     * Display name of the application (Settings page overrides config).
     */
    function app_name(): string
    {
        return (string) (setting('app_name') ?: config('app.name'));
    }
}
