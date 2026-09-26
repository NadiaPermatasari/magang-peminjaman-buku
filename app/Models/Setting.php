<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Default values, used until a setting is saved from a Settings page
     * (spec §21). Keep this in sync with the fields Super Admin can edit —
     * never add APP_KEY or other server-level secrets here.
     *
     * @var array<string, mixed>
     */
    public static array $defaults = [
        // Identitas Aplikasi
        'app_name' => null, // falls back to config('app.name')
        'app_short_name' => null,
        'institution_name' => null,
        'address' => null,
        'phone' => null,
        'email' => null,
        'description' => null,
        'footer_text' => null,

        // Branding
        'logo' => null,
        'login_logo' => null,
        'favicon' => null,
        'login_background' => null,
        'primary_color' => '#5e72e4', // Argon's original primary — see tailwind.config.js blue.500

        // Peminjaman
        'loan_duration_days' => 7,
        'max_active_loans' => 3,
        'pickup_deadline_days' => 2,
        'allow_renewal' => false,
        'max_renewals' => 1,
        'block_if_overdue' => true,

        // Denda
        'fine_enabled' => true,
        'fine_amount_per_day' => 1000,
        'fine_grace_period' => 0,
        'maximum_fine' => null,
        'block_if_unpaid_fine' => true,

        // Notifikasi
        'email_notification_enabled' => true,
        // Requires FONNTE_TOKEN to be configured server-side (.env) — this
        // toggle only controls whether the app *uses* the WhatsApp channel,
        // not whether it technically can (see config/services.php).
        'whatsapp_notification_enabled' => false,
        'due_reminder_days' => [3, 1, 0],
        'overdue_reminder_days' => [1, 3, 7],
        'pickup_reminder_hours' => 24,

        // Misc / UI
        'timezone' => 'Asia/Jakarta',
        'per_page' => 10,

        // Halaman Depan (public landing page, spec-adjacent — content
        // editable by Super Admin, not part of the original master spec)
        'landing_hero_title' => null, // falls back to app_name()
        'landing_hero_subtitle' => 'Kelola koleksi, keanggotaan, dan peminjaman buku dalam satu sistem yang cepat dan aman.',
        'landing_hero_image' => null,
        'landing_about_title' => 'Tentang Perpustakaan Kami',
        'landing_about_content' => null,
        'landing_meta_description' => null, // falls back to `description`
    ];

    /**
     * All settings as key => value (cached).
     *
     * @return array<string, mixed>
     */
    public static function all_(): array
    {
        return Cache::rememberForever('settings', function () {
            return static::query()->pluck('value', 'key')->map(function ($value) {
                $decoded = json_decode((string) $value, true);

                return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
            })->all();
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $values = static::all_();

        if (array_key_exists($key, $values) && $values[$key] !== null && $values[$key] !== '') {
            return $values[$key];
        }

        return $default ?? static::$defaults[$key] ?? null;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => is_scalar($value) ? $value : json_encode($value)]);

        Cache::forget('settings');
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public static function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => is_scalar($value) ? $value : json_encode($value)]);
        }

        Cache::forget('settings');
    }
}
