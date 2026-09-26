<?php

namespace App\Providers;

use App\Listeners\LogAuthenticationActivity;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Audit trail for auth/security events (login, logout, 2FA, ...).
        Event::subscribe(LogAuthenticationActivity::class);

        // Authorization is permission-driven (spatie/laravel-permission) via
        // Blade's @can('permission.name') / $user->can('permission.name') —
        // no blanket role-based Gate bypass is registered here on purpose,
        // so every policy's object-level checks (IDOR guards, spec §26) are
        // always evaluated, even for super-admin.

        // Rate limiting for heavy search and file-upload endpoints (spec §32).
        RateLimiter::for('search', function ($request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('upload', function ($request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });
    }
}
