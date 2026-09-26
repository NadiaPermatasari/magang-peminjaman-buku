<?php

use Laravel\Fortify\Features;

return [

    'guard' => 'web',

    'passwords' => 'users',

    'username' => 'email',

    'email' => 'email',

    'lowercase_usernames' => true,

    'home' => '/dashboard',

    /*
    |--------------------------------------------------------------------------
    | Redirects
    |--------------------------------------------------------------------------
    */

    'redirects' => [
        'login' => null,
        'logout' => '/login',
        'password-confirmation' => null,
        'register' => null,
        'email-verification' => null,
        'password-reset' => null,
    ],

    'prefix' => '',

    'domain' => null,

    // 'password-reset' rate-limits every Fortify route, closing a real gap:
    // Fortify does not throttle password.email/password.update itself
    // (spec §32 explicitly requires it for forgot/reset password).
    'middleware' => ['web', 'throttle:password-reset'],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting (spec §32)
    |--------------------------------------------------------------------------
    */

    'limiters' => [
        'login' => 'login',
        'two-factor' => 'two-factor',
    ],

    'views' => true,

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Public self-registration is intentionally disabled (spec §58 secure
    | default: "registration public = OFF"). Accounts — staff and anggota
    | alike — are created by an authorized user via the Users / Members
    | management screens, which assign a role explicitly. Passkeys are not
    | used by this application.
    |
    */

    'features' => [
        Features::resetPasswords(),
        Features::emailVerification(),
        Features::updateProfileInformation(),
        Features::updatePasswords(),
        // 'confirmPassword' => true requires re-entering the current password
        // (password.confirm) before enabling/disabling 2FA or regenerating
        // recovery codes — spec §6's sensitive-action re-authentication rule.
        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]),
    ],

];
