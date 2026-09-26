<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\AppNotification;
use App\Support\Activity;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Events\Dispatcher;
use Laravel\Fortify\Events\RecoveryCodesGenerated;
use Laravel\Fortify\Events\TwoFactorAuthenticationConfirmed;
use Laravel\Fortify\Events\TwoFactorAuthenticationDisabled;
use Laravel\Fortify\Events\TwoFactorAuthenticationFailed;

/**
 * Writes auth/security events to the append-only audit log (spec §24) and
 * tracks last login. Never log password/OTP/2FA secret/recovery code values.
 */
class LogAuthenticationActivity
{
    public function subscribe(Dispatcher $events): array
    {
        return [
            Login::class => 'onLogin',
            Logout::class => 'onLogout',
            Failed::class => 'onFailed',
            Registered::class => 'onRegistered',
            PasswordReset::class => 'onPasswordReset',
            TwoFactorAuthenticationConfirmed::class => 'onTwoFactorConfirmed',
            TwoFactorAuthenticationDisabled::class => 'onTwoFactorDisabled',
            TwoFactorAuthenticationFailed::class => 'onTwoFactorFailed',
            RecoveryCodesGenerated::class => 'onRecoveryCodesGenerated',
        ];
    }

    public function onTwoFactorConfirmed(TwoFactorAuthenticationConfirmed $event): void
    {
        Activity::log('2FA_ENABLED', 'Enabled two-factor authentication', $event->user, $event->user);
    }

    public function onTwoFactorDisabled(TwoFactorAuthenticationDisabled $event): void
    {
        Activity::log('2FA_DISABLED', 'Disabled two-factor authentication', $event->user, $event->user);
    }

    public function onTwoFactorFailed(TwoFactorAuthenticationFailed $event): void
    {
        Activity::log('2FA_FAILED', 'Failed two-factor authentication attempt', $event->user, $event->user);
    }

    public function onRecoveryCodesGenerated(RecoveryCodesGenerated $event): void
    {
        Activity::log('RECOVERY_CODES_REGENERATED', 'Regenerated two-factor recovery codes', $event->user, $event->user);
    }

    public function onLogin(Login $event): void
    {
        /** @var User $user */
        $user = $event->user;

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => request()?->ip(),
        ])->saveQuietly();

        Activity::log('LOGIN_SUCCESS', 'Signed in', $user, $user);
    }

    public function onFailed(Failed $event): void
    {
        $identifier = (string) ($event->credentials['email'] ?? '');

        Activity::log('LOGIN_FAILED', $identifier !== '' ? "Failed login attempt for {$identifier}" : 'Failed login attempt', $event->user instanceof User ? $event->user : null);
    }

    public function onLogout(Logout $event): void
    {
        if ($event->user instanceof User) {
            Activity::log('LOGOUT', 'Signed out', $event->user, $event->user);
        }
    }

    public function onRegistered(Registered $event): void
    {
        /** @var User $user */
        $user = $event->user;

        Activity::log('USER_CREATED', 'Created an account', $user, $user);

        $user->notify(new AppNotification(
            'Welcome to '.app_name().'!',
            'Complete your profile to get started.',
            'ni ni-like-2',
            route('profile'),
            'from-emerald-500 to-teal-400',
        ));
    }

    public function onPasswordReset(PasswordReset $event): void
    {
        /** @var User $user */
        $user = $event->user;

        Activity::log('PASSWORD_CHANGED', 'Reset password via email link', $user, $user);
    }
}
