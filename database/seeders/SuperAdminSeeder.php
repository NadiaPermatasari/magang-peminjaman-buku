<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Creates the initial super-admin account from env vars, never a hardcoded
 * password (spec §54). Idempotent — re-running just makes sure the account
 * exists and has the super-admin role; it never resets an existing password.
 */
class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SUPER_ADMIN_EMAIL');

        if (! $email) {
            $this->command?->warn('SUPER_ADMIN_EMAIL is not set in .env — skipping super-admin seeding. Set SUPER_ADMIN_EMAIL (and optionally SUPER_ADMIN_PASSWORD) and re-run: php artisan db:seed --class=SuperAdminSeeder');

            return;
        }

        $existing = User::withTrashed()->where('email', $email)->first();

        if ($existing) {
            $existing->assignRole('super-admin');
            $this->command?->info("Super-admin role ensured for existing user {$email}.");

            return;
        }

        $password = env('SUPER_ADMIN_PASSWORD');
        $generated = false;

        if (! $password) {
            $password = Str::password(16);
            $generated = true;
        }

        $user = User::create([
            'name' => env('SUPER_ADMIN_NAME', 'Super Admin'),
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        $user->assignRole('super-admin');

        if ($generated) {
            $this->command?->warn("Super-admin created: {$email} / {$password} — sign in and change this password immediately. Two-factor authentication is optional but strongly recommended for this role.");
        } else {
            $this->command?->info("Super-admin created: {$email} (password from SUPER_ADMIN_PASSWORD).");
        }
    }
}
