<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Seeds default application settings (spec §21) so the Settings screens
 * have concrete starting values. Idempotent: only fills settings that don't
 * exist yet, never overwrites a value an admin already changed.
 */
class ApplicationSettingSeeder extends Seeder
{
    public function run(): void
    {
        $existing = Setting::all_();

        foreach (Setting::$defaults as $key => $value) {
            if ($value === null || array_key_exists($key, $existing)) {
                continue;
            }

            Setting::set($key, $value);
        }
    }
}
