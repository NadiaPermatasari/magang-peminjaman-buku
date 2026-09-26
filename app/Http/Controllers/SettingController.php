<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\Activity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Application settings, Super Admin only (spec §21). One consolidated form
 * covering identity/branding/loan/fine/notification groups — split into
 * separate screens later if the form grows unwieldy.
 */
class SettingController extends Controller
{
    public function edit(Request $request)
    {
        $this->authorize('manage', Setting::class);

        $settings = [];
        foreach (array_keys(Setting::$defaults) as $key) {
            $settings[$key] = setting($key);
        }
        $settings['app_name'] = app_name();

        return view('settings.edit', [
            'settings' => $settings,
            'timezones' => \DateTimeZone::listIdentifiers(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorize('manage', Setting::class);

        $data = $request->validate([
            // Identitas Aplikasi
            'app_name' => ['required', 'string', 'max:60'],
            'app_short_name' => ['nullable', 'string', 'max:30'],
            'institution_name' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'footer_text' => ['nullable', 'string', 'max:160'],
            'timezone' => ['required', 'string', Rule::in(\DateTimeZone::listIdentifiers())],
            'per_page' => ['required', 'integer', 'min:5', 'max:100'],

            // Branding uploads — MIME is verified from file content, size capped,
            // filenames are randomized by Storage::store() (spec §22).
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
            'login_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
            'favicon' => ['nullable', 'image', 'mimes:png', 'max:256'],
            'login_background' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'primary_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],

            // Peminjaman
            'loan_duration_days' => ['required', 'integer', 'min:1', 'max:365'],
            'max_active_loans' => ['required', 'integer', 'min:1', 'max:50'],
            'pickup_deadline_days' => ['required', 'integer', 'min:1', 'max:30'],
            'allow_renewal' => ['nullable', 'boolean'],
            'max_renewals' => ['required', 'integer', 'min:0', 'max:10'],
            'block_if_overdue' => ['nullable', 'boolean'],

            // Denda
            'fine_enabled' => ['nullable', 'boolean'],
            'fine_amount_per_day' => ['required', 'integer', 'min:0'],
            'fine_grace_period' => ['required', 'integer', 'min:0', 'max:30'],
            'maximum_fine' => ['nullable', 'integer', 'min:0'],
            'block_if_unpaid_fine' => ['nullable', 'boolean'],

            // Notifikasi
            'email_notification_enabled' => ['nullable', 'boolean'],
            'whatsapp_notification_enabled' => ['nullable', 'boolean'],
            'due_reminder_days' => ['nullable', 'string'],
            'overdue_reminder_days' => ['nullable', 'string'],
            'pickup_reminder_hours' => ['required', 'integer', 'min:1', 'max:240'],

            // Halaman Depan (landing page)
            'landing_hero_title' => ['nullable', 'string', 'max:100'],
            'landing_hero_subtitle' => ['nullable', 'string', 'max:255'],
            'landing_hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'landing_about_title' => ['nullable', 'string', 'max:100'],
            'landing_about_content' => ['nullable', 'string', 'max:2000'],
            'landing_meta_description' => ['nullable', 'string', 'max:160'],
        ]);

        foreach (['logo', 'login_logo', 'favicon', 'login_background', 'landing_hero_image'] as $field) {
            if ($request->hasFile($field)) {
                $old = setting($field);
                if ($old) {
                    Storage::disk('public')->delete($old);
                }
                $data[$field] = $request->file($field)->store('branding', 'public');
            } else {
                unset($data[$field]);
            }
        }

        foreach (['allow_renewal', 'block_if_overdue', 'fine_enabled', 'block_if_unpaid_fine', 'email_notification_enabled', 'whatsapp_notification_enabled'] as $field) {
            $data[$field] = $request->boolean($field);
        }

        foreach (['due_reminder_days', 'overdue_reminder_days'] as $field) {
            if (isset($data[$field])) {
                $data[$field] = collect(explode(',', $data[$field]))
                    ->map(fn ($v) => (int) trim($v))
                    ->filter(fn ($v) => $v >= 0)
                    ->unique()
                    ->sort()
                    ->values()
                    ->all();
            }
        }

        Setting::setMany($data);

        Activity::log('SETTING_CHANGED', 'Updated application settings');

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
