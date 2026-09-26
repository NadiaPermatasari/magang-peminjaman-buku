<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Activity;
use App\Support\UserAgent;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Profile page: edit profile, avatar, password, email verification,
     * two-factor authentication, browser sessions and account deletion.
     *
     * The profile / password forms submit to Fortify's routes
     * (user-profile-information.update, user-password.update); 2FA uses
     * Fortify's two-factor.* routes.
     */
    public function edit(Request $request)
    {
        $user = $request->user();

        return view('pages.profile', [
            'user' => $user,
            'stats' => [
                'activities' => $user->activities()->count(),
                'notifications' => $user->unreadNotifications()->count(),
            ],
            'sessions' => $this->sessions($request),
            'recentActivities' => $user->activities()->take(5)->get(),
        ]);
    }

    /**
     * Upload a new avatar (jpg/png/webp, max 2 MB).
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();

        if ($user->avatar && ! str_starts_with($user->avatar, 'http')) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->forceFill(['avatar' => $path])->save();

        Activity::log('AVATAR_UPDATED', 'Changed profile photo', $user);

        return back()->with('success', 'Foto profil diperbarui.');
    }

    public function destroyAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->avatar && ! str_starts_with($user->avatar, 'http')) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->forceFill(['avatar' => null])->save();

        return back()->with('success', 'Foto profil dihapus.');
    }

    /**
     * Log out every other browser session (requires the current password).
     */
    public function logoutOtherSessions(Request $request): RedirectResponse
    {
        $request->validateWithBag('logoutOtherSessions', [
            'password' => ['required', 'current_password'],
        ]);

        Auth::logoutOtherDevices($request->password);

        if (config('session.driver') === 'database') {
            DB::table('sessions')
                ->where('user_id', $request->user()->id)
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }

        Activity::log('SESSIONS_LOGGED_OUT', 'Logged out other browser sessions', $request->user());

        return back()->with('success', 'Sesi lain berhasil dikeluarkan.');
    }

    /**
     * Deactivate the signed in user's account (soft delete — reversible by
     * an administrator, never a hard delete; spec §23/§69).
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('deleteAccount', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        if ($user->hasRole('super-admin') && User::role('super-admin')->count() === 1) {
            return back()->with('error', 'Anda satu-satunya super-admin. Tetapkan super-admin lain sebelum menghapus akun Anda.');
        }

        Activity::log('ACCOUNT_DELETED', "Deleted own account ({$user->email})", null, $user);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Akun Anda telah dihapus.');
    }

    /**
     * Browser sessions from the database session driver.
     *
     * @return Collection<int, object>
     */
    protected function sessions(Request $request)
    {
        if (config('session.driver') !== 'database') {
            return collect();
        }

        return DB::table('sessions')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('last_activity')
            ->get()
            ->map(function ($session) use ($request) {
                $agent = UserAgent::parse($session->user_agent);

                return (object) [
                    'ip_address' => $session->ip_address,
                    'is_current' => $session->id === $request->session()->getId(),
                    'device' => $agent->device(),
                    'platform' => $agent->platform(),
                    'browser' => $agent->browser(),
                    'last_active' => Carbon::createFromTimestamp($session->last_activity)->diffForHumans(),
                ];
            });
    }
}
